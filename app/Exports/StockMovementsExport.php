<?php

namespace App\Exports;

use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
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
            ->latest();

        if (!$this->canManageWarehouse) {
            $query->where('technician_id', auth()->id());
        }

        if (!empty($this->filters['item_id'])) {
            $query->where('item_id', $this->filters['item_id']);
        }

        if (!empty($this->filters['technician_id'])) {
            $query->where('technician_id', $this->filters['technician_id']);
        }

        if (!empty($this->filters['movement_type'])) {
            $query->where('movement_type', $this->filters['movement_type']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
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
