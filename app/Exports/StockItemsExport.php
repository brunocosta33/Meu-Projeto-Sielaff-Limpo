<?php

namespace App\Exports;

use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockItemsExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(private array $filters = [])
    {
    }

    public function collection()
    {
        $query = Item::query()
            ->withSum('technicianStocks as technician_stock_total', 'quantity')
            ->orderBy('reference');

        if (!empty($this->filters['q'])) {
            $term = trim((string) $this->filters['q']);

            $query->where(function (Builder $builder) use ($term) {
                $builder->where('reference', 'like', '%' . $term . '%')
                    ->orWhere('name', 'like', '%' . $term . '%')
                    ->orWhere('description', 'like', '%' . $term . '%');
            });
        }

        if (($this->filters['is_active'] ?? '') !== '') {
            $query->where('is_active', $this->filters['is_active'] === '1');
        }

        if (!empty($this->filters['low_stock'])) {
            $query->whereColumn('warehouse_stock', '<=', 'minimum_stock');
        }

        return $query->get()->map(function (Item $item) {
            $technicianStockTotal = (int) ($item->technician_stock_total ?? 0);

            return [
                __('Referência') => $item->reference,
                __('Nome') => $item->name,
                __('Stock Armazém') => $item->warehouse_stock,
                __('Stock Mínimo') => $item->minimum_stock,
                __('Stock Técnicos') => $technicianStockTotal,
                __('Stock Total') => (int) $item->warehouse_stock + $technicianStockTotal,
            ];
        });
    }

    public function headings(): array
    {
        return [__('Referência'), __('Nome'), __('Stock Armazém'), __('Stock Mínimo'), __('Stock Técnicos'), __('Stock Total')];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle(1)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '0F5BCF']],
        ]);

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
