<?php

namespace App\Exports;

use App\Exports\Concerns\FolhaComEstilo;
use App\Http\Controllers\TechnicalRequestController;
use App\Models\TechnicalRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Pedidos técnicos em Excel, com os filtros que estão à vista na página.
 * Mesmo aspeto das outras exportações (FolhaComEstilo), com cor no estado,
 * na prioridade e na insígnia, e as datas como datas a sério (dá para ordenar e filtrar).
 */
class TechnicalRequestsExport implements FromCollection, WithHeadings, WithEvents, WithTitle, WithColumnWidths, WithColumnFormatting, WithStrictNullComparison
{
    use FolhaComEstilo;

    //  A Nº           E Cidade        I Tipo de serviço  M Estado        Q Observações
    //  B Data pedido  F Zona          J Origem           N Responsável
    //  C Insígnia     G Nºs de série  K Descrição        O Agendamento
    //  D Loja         H Modelos       L Prioridade       P Resolução

    /** O estado, a prioridade e o cliente de cada linha (pela ordem), para as cores. */
    private array $marcas = [];

    private const CORES_ESTADO = [
        'pendente' => ['FEF3C7', '92400E'],
        'agendado' => ['DBEAFE', '1E40AF'],
        'aguarda_peca' => ['FFEDD5', '9A3412'],
        'concluido' => ['DCFCE7', '166534'],
        'cancelado' => ['F1F5F9', '64748B'],
    ];

    private const CORES_PRIORIDADE = [
        'baixa' => ['E0F2FE', '075985'],
        'media' => ['FEF9C3', '854D0E'],
        'alta' => ['FEE2E2', 'B91C1C'],
    ];

    public function __construct(
        private ?User $technician = null,
        private array $filters = []
    ) {
    }

    public function title(): string
    {
        return $this->technician
            ? \Illuminate\Support\Str::limit(__('Pedidos') . ' - ' . $this->technician->name, 31, '')
            : __('Pedidos técnicos');
    }

    protected function colunasCentradas(): array
    {
        return ['A', 'B', 'C', 'F', 'G', 'L', 'M', 'O', 'P'];
    }

    protected function colunasLargas(): array
    {
        return ['D', 'G', 'H', 'K', 'Q'];
    }

    public function columnWidths(): array
    {
        return ['A' => 8, 'B' => 12, 'C' => 12, 'D' => 30, 'E' => 16, 'F' => 10, 'G' => 18, 'H' => 22,
            'I' => 20, 'J' => 16, 'K' => 48, 'L' => 12, 'M' => 15, 'N' => 20, 'O' => 17, 'P' => 17, 'Q' => 40];
    }

    public function columnFormats(): array
    {
        return ['B' => 'dd/mm/yyyy', 'O' => 'dd/mm/yyyy hh:mm', 'P' => 'dd/mm/yyyy hh:mm'];
    }

    public function collection()
    {
        $query = TechnicalRequest::with(['store', 'machine', 'machines.machine', 'assignedTechnician']);

        if ($this->technician) {
            $query->where('assigned_technician_id', $this->technician->id);
        }

        if (!empty($this->filters['q'])) {
            $term = trim($this->filters['q']);

            $query->where(function (Builder $q) use ($term) {
                $q->where('origem', 'like', '%' . $term . '%')
                    ->orWhere('descricao_problema', 'like', '%' . $term . '%')
                    ->orWhere('observacoes', 'like', '%' . $term . '%')
                    ->orWhereHas('assignedTechnician', function (Builder $technicianQuery) use ($term) {
                        $technicianQuery->where('name', 'like', '%' . $term . '%')
                            ->orWhere('email', 'like', '%' . $term . '%');
                    })
                    ->orWhereHas('store', function (Builder $storeQuery) use ($term) {
                        $storeQuery->where('codigo_loja', 'like', '%' . $term . '%')
                            ->orWhere('nome_loja', 'like', '%' . $term . '%')
                            ->orWhere('insignia', 'like', '%' . $term . '%')
                            ->orWhere('cidade', 'like', '%' . $term . '%');
                    })
                    ->orWhereHas('machine', function (Builder $machineQuery) use ($term) {
                        $machineQuery->where('serial_number', 'like', '%' . $term . '%');
                    });
            });
        }

        if (!empty($this->filters['codigo_loja'])) {
            $codigoLoja = trim($this->filters['codigo_loja']);
            $query->whereHas('store', function (Builder $storeQuery) use ($codigoLoja) {
                $storeQuery->where('codigo_loja', 'like', '%' . $codigoLoja . '%');
            });
        }

        $selectedStatuses = array_values(array_filter((array) ($this->filters['estado'] ?? [])));

        if (!empty($selectedStatuses)) {
            $query->whereIn('estado', $selectedStatuses);
        }

        if (!empty($this->filters['open_only'])) {
            $query->where('estado', '!=', 'concluido');
        }

        if (!empty($this->filters['prioridade'])) {
            $query->where('prioridade', $this->filters['prioridade']);
        }

        if (!empty($this->filters['zona'])) {
            $query->where('zona', $this->filters['zona']);
        }

        if (!empty($this->filters['tipo_servico'])) {
            $query->where('tipo_servico', $this->filters['tipo_servico']);
        }

        if (!empty($this->filters['assigned_technician_id']) && !$this->technician) {
            if ($this->filters['assigned_technician_id'] === 'unassigned') {
                $query->whereNull('assigned_technician_id');
            } else {
                $query->where('assigned_technician_id', $this->filters['assigned_technician_id']);
            }
        }

        if (!empty($this->filters['serial_number'])) {
            $serialNumber = trim($this->filters['serial_number']);
            $query->whereHas('machine', function (Builder $machineQuery) use ($serialNumber) {
                $machineQuery->where('serial_number', 'like', '%' . $serialNumber . '%');
            });
        }

        if (!empty($this->filters['mes'])) {
            $monthDate = Carbon::createFromFormat('Y-m', $this->filters['mes']);
            $query->whereBetween('data_pedido', [
                $monthDate->copy()->startOfMonth()->toDateString(),
                $monthDate->copy()->endOfMonth()->toDateString(),
            ]);
        } else {
            if (!empty($this->filters['data_inicio'])) {
                $query->whereDate('data_pedido', '>=', $this->filters['data_inicio']);
            }

            if (!empty($this->filters['data_fim'])) {
                $query->whereDate('data_pedido', '<=', $this->filters['data_fim']);
            }
        }

        $pedidos = $query->orderByDesc('data_pedido')->orderByDesc('id')->get();

        $data = fn ($valor) => $valor ? ExcelDate::PHPToExcel(Carbon::parse($valor)) : null;

        return $pedidos->map(function (TechnicalRequest $req) use ($data) {
            $maquinas = collect([$req->machine])
                ->merge($req->machines->map(fn ($m) => $m->machine))
                ->filter()
                ->unique('id');

            $this->marcas[] = [
                'estado' => $req->estado,
                'prioridade' => $req->prioridade,
                'cliente' => $req->store?->clienteConfig(),
            ];

            return [
                $req->id,
                $data($req->data_pedido),
                $req->store?->insignia ? ucfirst($req->store->insignia) : '',
                $req->store
                    ? trim(($req->store->codigo_loja ?: '-') . ' - ' . ($req->store->nome_loja ?: '-'))
                    : __('Sem loja associada'),
                $req->store?->cidade,
                $req->zona ? __(TechnicalRequestController::ZONES[$req->zona] ?? ucfirst($req->zona)) : '',
                $maquinas->pluck('serial_number')->filter()->implode("\n"),
                $maquinas->map(fn ($m) => $m->descricao ?: $m->modelo)->filter()->unique()->implode("\n"),
                $req->tipo_servico ? __(TechnicalRequestController::SERVICE_TYPES[$req->tipo_servico] ?? $req->tipo_servico) : '',
                $req->origem,
                $req->descricao_problema,
                __(TechnicalRequestController::PRIORITIES[$req->prioridade] ?? ucfirst((string) $req->prioridade)),
                __(TechnicalRequestController::STATUSES[$req->estado] ?? ucfirst((string) $req->estado)),
                $req->assignedPersonLabel(),
                $data($req->data_agendamento),
                $data($req->data_resolucao),
                $req->observacoes,
            ];
        });
    }

    public function headings(): array
    {
        return [
            __('Nº'), __('Data Pedido'), __('Insígnia'), __('Loja'), __('Cidade'), __('Zona'),
            __('Nºs de série'), __('Modelos'), __('Tipo de Serviço'), __('Origem'), __('Descrição'),
            __('Prioridade'), __('Estado'), __('Responsável atribuído'),
            __('Data Agendamento'), __('Data Resolução'), __('Observações'),
        ];
    }

    /** Estado e prioridade em "etiqueta" de cor; a insígnia com a cor do cliente. */
    protected function estiloExtra(Worksheet $folha, int $ultimaLinha, string $ultimaColuna): void
    {
        $etiqueta = function (string $celula, ?array $cores) use ($folha) {
            if (!$cores) {
                return;
            }
            [$fundo, $letra] = $cores;
            $folha->getStyle($celula)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $letra]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fundo]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'indent' => 0],
            ]);
        };

        foreach ($this->marcas as $i => $marca) {
            $linha = $i + 2;
            $etiqueta('M' . $linha, self::CORES_ESTADO[$marca['estado']] ?? null);
            $etiqueta('L' . $linha, self::CORES_PRIORIDADE[$marca['prioridade']] ?? null);

            if ($cliente = $marca['cliente']) {
                $cor = strtoupper(ltrim($cliente['color'], '#'));
                $letra = strtoupper(ltrim($cliente['texto'], '#'));
                $etiqueta('C' . $linha, [$cor, $letra]);
            }

            // Os nºs de série são texto (senão o Excel trata-os como números e encosta-os à direita)
            $serie = $folha->getCell('G' . $linha);
            $serie->setValueExplicit((string) $serie->getValue(), DataType::TYPE_STRING);

            // O nº de série e o nº do pedido a negrito: é o que se procura primeiro
            $folha->getStyle('A' . $linha)->getFont()->setBold(true);
            $folha->getStyle('G' . $linha)->getFont()->setBold(true)->getColor()->setRGB('B45309');
        }
    }
}
