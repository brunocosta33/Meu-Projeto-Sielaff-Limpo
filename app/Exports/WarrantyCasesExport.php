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
            __('Nº'), __('Data da avaria'), __('Tipo'), __('Cliente'), __('Loja'), __('Nº Série'), __('Problema'), __('Fase'),
            __('Nº ordem de serviço'), __('Data da visita'), __('Diagnóstico do técnico'),
            __('Cotação pedida'), __('Nº cotação'), __('Cotação recebida'), __('Enviada ao cliente'),
            __('Nº ordem de compra'), __('Ordem de compra recebida'),
            __('Nº do artigo'), __('Nº do cliente'), __('Pedido enviado para a Alemanha'), __('Peça confirmada'), __('Peça'), __('Fechado em'), __('Ordem de serviço'), __('Motivo da recusa'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
