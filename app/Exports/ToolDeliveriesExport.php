<?php

namespace App\Exports;

use App\Models\ToolDelivery;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Entregas em Excel, com os mesmos filtros que estão à vista. */
class ToolDeliveriesExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(private $query)
    {
    }

    public function collection()
    {
        return $this->query->with(['technician', 'creator', 'proalphaUser'])
            ->orderBy('ocorrido_em')->orderBy('id')->get()
            ->map(fn (ToolDelivery $e) => [
                optional($e->ocorrido_em)->format('d/m/Y'),
                optional($e->ocorrido_em)->format('H:i'),
                $e->tipoLabel(),
                $e->technician->name ?? '',
                $e->codigo,
                $e->artigo,
                (int) $e->quantidade,
                $e->isProalpha() ? __('Sim') : __('Não'),
                $e->isProalpha() ? $e->proalpha_em->format('d/m/Y H:i') : '',
                $e->proalphaUser->name ?? '',
                $e->observacoes,
                $e->creator->name ?? '',
            ]);
    }

    public function headings(): array
    {
        return [
            __('Data'), __('Hora'), __('Tipo'), __('Técnico'), __('Código'), __('Peça ou ferramenta'),
            __('Quantidade'), __('Lançado no ProAlpha'), __('Lançado em'), __('Lançado por'),
            __('Observações'), __('Registado por'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
