<?php

namespace App\Exports;

use App\Models\HrExpense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Despesas em Excel, com os mesmos filtros que estão à vista na página. */
class HrExpensesExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(private $query)
    {
    }

    public function collection()
    {
        $date = fn ($d) => $d ? $d->format('d/m/Y') : '';

        return $this->query->with(['staff.user', 'decider', 'payer'])->withCount('files')
            ->orderBy('data')->orderBy('id')->get()
            ->map(fn (HrExpense $e) => [
                $e->id,
                $date($e->data),
                $e->staff->user->name ?? '',
                $e->categoriaLabel(),
                $e->descricao,
                // Número, para o Excel poder somar
                (float) $e->valor,
                $e->stateLabel(),
                $e->decider->name ?? '',
                $date($e->decided_at),
                $e->motivo_rejeicao,
                $e->isPaid() ? __('Sim') : __('Não'),
                $date($e->pago_em),
                $e->paymentLabel(),
                $e->payer->name ?? '',
                $e->files_count,
            ]);
    }

    public function headings(): array
    {
        return [
            __('Nº'), __('Data'), __('Colaborador'), __('Categoria'), __('Descrição'), __('Valor'), __('Estado'),
            __('Decidida por'), __('Decidida em'), __('Motivo da rejeição'),
            __('Já paga'), __('Paga em'), __('Forma de pagamento'), __('Paga por'), __('Comprovativos'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
