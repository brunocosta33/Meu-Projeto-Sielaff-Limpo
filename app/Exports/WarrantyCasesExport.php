<?php

namespace App\Exports;

use App\Models\WarrantyCase;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WarrantyCasesExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(private $query)
    {
    }

    public function collection()
    {
        $date = fn ($d) => $d ? $d->format('d/m/Y') : '';

        return $this->query->with(['store', 'machine'])->orderBy('created_at')->get()->map(fn (WarrantyCase $c) => [
            $c->id,
            $date($c->data_avaria),
            $c->clientLabel(),
            trim(($c->store->codigo_loja ?? '') . ' - ' . ($c->store->nome_loja ?? ''), ' -'),
            $c->machine->serial_number ?? '',
            $c->descricao,
            __($c->phase()['label']),
            $date($c->cotacao_pedida_em),
            $c->cotacao_numero,
            $date($c->cotacao_recebida_em),
            $date($c->enviada_cliente_em),
            $c->oc_numero,
            $date($c->oc_recebida_em),
            $date($c->fechado_em),
            $c->motivo_recusa,
        ]);
    }

    public function headings(): array
    {
        return [
            __('Nº'), __('Data da avaria'), __('Cliente'), __('Loja'), __('Nº Série'), __('Problema'), __('Fase'),
            __('Cotação pedida'), __('Nº cotação'), __('Cotação recebida'), __('Enviada ao cliente'),
            __('Nº ordem de compra'), __('Ordem de compra recebida'), __('Fechado em'), __('Motivo da recusa'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
