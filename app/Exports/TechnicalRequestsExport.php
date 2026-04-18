<?php

namespace App\Exports;

use App\Models\TechnicalRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TechnicalRequestsExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    public function __construct(
        private ?User $technician = null,
        private array $filters = []
    )
    {
    }

    public function collection()
    {
        $query = TechnicalRequest::with(['store', 'assignedTechnician']);

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

        if (!empty($this->filters['estado'])) {
            $query->whereIn('estado', (array) $this->filters['estado']);
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

        return $query
            ->get()
            ->map(function ($req) {
                return [
                    'ID'               => $req->id,
                    'Loja'             => $req->store->codigo_loja . ' - ' . $req->store->nome_loja,
                    'Resolvido por'    => $req->assignedPersonLabel(),
                    'Estado'           => ucfirst($req->estado),
                    'Origem'           => $req->origem,
                    'Tipo de Serviço'  => $req->tipo_servico,
                    'Zona'             => $req->zona ? ucfirst($req->zona) : '',
                    'Descrição'        => $req->descricao_problema,
                    'Prioridade'       => ucfirst($req->prioridade),
                    'Data Pedido'      => $req->data_pedido ? Carbon::parse($req->data_pedido)->format('d/m/Y') : '',
                    'Data Agendamento' => $req->data_agendamento ? Carbon::parse($req->data_agendamento)->format('d/m/Y H:i') : '',
                    'Data Resolução'   => $req->data_resolucao ? Carbon::parse($req->data_resolucao)->format('d/m/Y H:i') : '',
                    'Observações'      => $req->observacoes,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Loja',
            'Resolvido por',
            'Estado',
            'Origem',
            'Tipo de Serviço',
            'Zona',
            'Descrição',
            'Prioridade',
            'Data Pedido',
            'Data Agendamento',
            'Data Resolução',
            'Observações',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Cabeçalho (linha 1)
        $sheet->getStyle(1)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'], // texto branco
                'size' => 12,
            ],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => '4CAF50'], // verde
            ],
            'alignment' => [
                'horizontal' => 'center',
            ],
        ]);

        // Largura automática
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowCount = $sheet->getHighestRow();

                // Iterar pelas linhas (começa em 2 porque 1 é cabeçalho)
                for ($row = 2; $row <= $rowCount; $row++) {
                    $estado = strtolower($sheet->getCell("D$row")->getValue()); // Estado está na coluna D
                    $prioridade = strtolower($sheet->getCell("I$row")->getValue()); // Prioridade está na coluna I
                    $dataAgendamento = $sheet->getCell("K$row")->getValue(); // Data Agendamento na coluna K
                    $dataResolucao   = $sheet->getCell("L$row")->getValue(); // Data Resolução na coluna L

                    // --- Cores para ESTADO ---
                    switch ($estado) {
                        case 'pendente':
                            $colorEstado = 'FFF59D'; // amarelo claro
                            break;
                        case 'agendado':
                            $colorEstado = '81D4FA'; // azul claro
                            break;
                        case 'concluido':
                            $colorEstado = 'A5D6A7'; // verde claro
                            break;
                        case 'cancelado':
                            $colorEstado = 'EF9A9A'; // vermelho claro
                            break;
                        case 'aguarda peca':
                            $colorEstado = 'FF7043'; // laranja
                            break;
                        default:
                            $colorEstado = null;
                    }

                    if ($colorEstado) {
                        $sheet->getStyle("D$row")->applyFromArray([
                            'fill' => [
                                'fillType' => 'solid',
                                'color' => ['rgb' => $colorEstado],
                            ],
                        ]);
                    }

                    // --- Cores para PRIORIDADE ---
                    switch ($prioridade) {
                        case 'baixa':
                            $colorPrioridade = '81D4FA'; // azul claro
                            break;
                        case 'media':
                            $colorPrioridade = 'FFF59D'; // amarelo claro
                            break;
                        case 'alta':
                            $colorPrioridade = 'EF9A9A'; // vermelho claro
                            break;
                        default:
                            $colorPrioridade = null;
                    }

                    if ($colorPrioridade) {
                        $sheet->getStyle("I$row")->applyFromArray([
                            'fill' => [
                                'fillType' => 'solid',
                                'color' => ['rgb' => $colorPrioridade],
                            ],
                        ]);
                    }

                    // --- Cor para Data Agendamento (coluna I) ---
                    if (!empty($dataAgendamento)) {
                        $sheet->getStyle("K$row")->applyFromArray([
                            'fill' => [
                                'fillType' => 'solid',
                                'color' => ['rgb' => 'FFF59D'], // amarelo
                            ],
                        ]);
                    }

                    // --- Cor para Data Resolução (coluna J) ---
                    if (!empty($dataResolucao)) {
                        $sheet->getStyle("L$row")->applyFromArray([
                            'fill' => [
                                'fillType' => 'solid',
                                'color' => ['rgb' => 'A5D6A7'], // verde
                            ],
                        ]);
                    }
                }
            },
        ];
    }
}
