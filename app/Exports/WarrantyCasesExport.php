<?php

namespace App\Exports;

use App\Exports\Concerns\FolhaComEstilo;
use App\Models\WarrantyCase;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/** Processos fora da garantia, com o ponto de situação logo a seguir ao número. */
class WarrantyCasesExport implements FromCollection, WithHeadings, WithEvents, WithTitle, WithColumnWidths
{
    use FolhaComEstilo;

    public function __construct(private $query)
    {
    }

    public function title(): string
    {
        return __('Fora da Garantia');
    }

    protected function colunasCentradas(): array
    {
        return ['A', 'C', 'D', 'E', 'F', 'H', 'L', 'N', 'P', 'Q', 'S', 'V', 'W', 'Y'];
    }

    protected function colunasLargas(): array
    {
        return ['B', 'G', 'I', 'M', 'AA'];
    }

    public function columnWidths(): array
    {
        return ['A' => 7, 'B' => 45, 'C' => 20, 'D' => 12, 'E' => 12, 'F' => 10, 'G' => 28, 'H' => 13, 'I' => 40,
            'J' => 22, 'K' => 14, 'L' => 12, 'M' => 40, 'N' => 12, 'O' => 14, 'P' => 12, 'Q' => 12, 'R' => 16,
            'S' => 12, 'T' => 16, 'U' => 14, 'V' => 12, 'W' => 12, 'X' => 18, 'Y' => 12, 'Z' => 18, 'AA' => 30];
    }

    public function collection()
    {
        $date = fn ($d) => $d ? $d->format('d/m/Y') : '';

        return $this->query->with(['store', 'machine', 'latestNote.user'])->orderBy('created_at')->get()->map(fn (WarrantyCase $c) => [
            $c->id,
            $c->latestNote?->texto ?? '',
            $c->latestNote ? $c->latestNote->created_at->format('d/m/Y') . ' · ' . ($c->latestNote->user->name ?? '') : '',
            $date($c->data_avaria),
            $c->typeLabel(),
            $c->clientLabel(),
            trim(($c->store->codigo_loja ?? '') . ' - ' . ($c->store->nome_loja ?? ''), ' -'),
            $c->machine->serial_number ?? '',
            $c->descricao,
            __($c->phase()['label']),
            $c->os_numero,
            $date($c->os_em),
            $c->os_texto,
            $date($c->cotacao_pedida_em),
            $c->cotacao_numero,
            $date($c->cotacao_recebida_em),
            $date($c->enviada_cliente_em),
            $c->oc_numero,
            $date($c->oc_recebida_em),
            $c->artigo_numero,
            $c->cliente_numero,
            $date($c->alemanha_enviado_em),
            $date($c->peca_recebida_em),
            $c->partOriginLabel(),
            $date($c->fechado_em),
            $c->servico_nome ?: ($c->fase === 'concluido' ? __('sem PDF') : ''),
            $c->motivo_recusa,
        ]);
    }

    public function headings(): array
    {
        return [
            __('Nº'), __('Ponto de situação'), __('Escrito em'), __('Data da avaria'), __('Tipo'), __('Cliente'), __('Loja'), __('Nº Série'), __('Problema'), __('Fase'),
            __('Nº ordem de serviço'), __('Data da visita'), __('Diagnóstico do técnico'),
            __('Cotação pedida'), __('Nº cotação'), __('Cotação recebida'), __('Enviada ao cliente'),
            __('Nº ordem de compra'), __('Ordem de compra recebida'),
            __('Nº do artigo'), __('Nº do cliente'), __('Pedido enviado para a Alemanha'), __('Peça confirmada'), __('Peça'), __('Fechado em'), __('Ordem de serviço'), __('Motivo da recusa / cancelamento'),
        ];
    }
}
