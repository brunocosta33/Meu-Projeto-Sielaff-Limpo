<?php

namespace App\Exports;

use App\Models\TechnicianItemStock;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TechnicianStocksExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(private Collection $technicians, private bool $canManageWarehouse = true)
    {
    }

    public function collection()
    {
        $technicianIds = $this->technicians->pluck('id');

        return TechnicianItemStock::query()
            ->with(['item', 'technician'])
            ->where('quantity', '>', 0)
            ->whereIn('technician_id', $technicianIds)
            ->orderBy('technician_id')
            ->get()
            ->map(function (TechnicianItemStock $stock) {
                return [
                    __('Técnico') => $stock->technician->name ?? $stock->technician->email ?? '',
                    __('Referência') => $stock->item->reference ?? '',
                    __('Peça') => $stock->item->name ?? '',
                    __('Quantidade') => $stock->quantity,
                ];
            });
    }

    public function headings(): array
    {
        return [__('Técnico'), __('Referência'), __('Peça'), __('Quantidade')];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle(1)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '6F42C1']],
        ]);

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
