<?php

namespace App\Exports;

use App\Models\StockMovement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockMovementsExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(private array $filters = [], private bool $canManageWarehouse = true)
    {
    }

    public function collection()
    {
        $query = StockMovement::query()
            ->with(['item', 'technician', 'creator'])
            ->applyFilters($this->filters)
            ->latest();

        if (!$this->canManageWarehouse) {
            $query->where('technician_id', auth()->id());
        }

        return $query->get()->map(function (StockMovement $movement) {
            return [
                __('Data') => optional($movement->created_at)->format('d/m/Y H:i'),
                __('Referência') => $movement->item->reference ?? '',
                __('Peça') => $movement->item->name ?? '',
                __('Tipo') => __(StockMovement::TYPES[$movement->movement_type] ?? $movement->movement_type),
                __('Técnico') => $movement->technician->name ?? $movement->technician->email ?? '',
                __('Quantidade') => $movement->quantity,
                __('Origem') => $movement->source ?? '',
                __('Destino') => $movement->destination ?? '',
                __('Notas') => $movement->notes ?? '',
                __('Criado por') => $movement->creator->name ?? $movement->creator->email ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [__('Data'), __('Referência'), __('Peça'), __('Tipo'), __('Técnico'), __('Quantidade'), __('Origem'), __('Destino'), __('Notas'), __('Criado por')];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle(1)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '198754']],
        ]);

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
