<?php

namespace App\Exports;

use App\Exports\Concerns\FolhaComEstilo;
use App\Models\HrExpense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Despesas em Excel, com os mesmos filtros que estão à vista na página. */
class HrExpensesExport implements FromCollection, WithHeadings, WithEvents, WithTitle, WithColumnWidths, WithStrictNullComparison, ShouldAutoSize
{
    use FolhaComEstilo;

    //  A  Nº        F  Valor         K  Já paga
    //  B  Data      G  Estado        L  Paga em
    //  C  Colab.    H  Decidida por  M  Forma
    //  D  Categoria I  Decidida em   N  Paga por
    //  E  Descrição J  Motivo        O  Comprovativos
    protected function colunasDinheiro(): array
    {
        return ['F'];
    }

    protected function colunasCentradas(): array
    {
        return ['A', 'B', 'I', 'K', 'L', 'O'];
    }

    protected function colunasLargas(): array
    {
        return ['E', 'J'];
    }

    public function __construct(private $query)
    {
    }

    public function title(): string
    {
        return __('Despesas');
    }

    /** As de texto corrido levam largura fixa; as outras ajustam-se ao conteúdo. */
    public function columnWidths(): array
    {
        return [
            'A' => 7, 'B' => 12, 'D' => 22, 'E' => 34, 'F' => 12, 'G' => 13,
            'I' => 13, 'J' => 30, 'K' => 10, 'L' => 12, 'M' => 18, 'O' => 14,
        ];
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

    /** O estado e o pagamento ficam com a mesma cor que têm na página. */
    protected function estiloExtra(Worksheet $folha, int $ultimaLinha, string $ultimaColuna): void
    {
        $cores = [
            __('Pendente') => ['FFF4CC', '7A5600'],
            __('Aprovada') => ['DFF0E0', '1B5E20'],
            __('Rejeitada') => ['FBDCDC', 'A31515'],
        ];
        $sim = __('Sim');

        // Ler tudo primeiro: em PhpSpreadsheet os estilos são partilhados e ler
        // no meio de escritas faz a cor de uma célula saltar para outra
        $valores = [];
        for ($linha = 2; $linha <= $ultimaLinha; $linha++) {
            $valores[$linha] = [
                'estado' => (string) $folha->getCell('G' . $linha)->getValue(),
                'paga' => (string) $folha->getCell('K' . $linha)->getValue(),
            ];
        }

        foreach ($valores as $linha => $v) {
            if (isset($cores[$v['estado']])) {
                [$fundo, $letra] = $cores[$v['estado']];
                $folha->getStyle('G' . $linha)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $letra]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fundo]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'indent' => 0],
                ]);
            }

            $folha->getStyle('K' . $linha)->applyFromArray([
                'font' => [
                    'bold' => $v['paga'] === $sim,
                    'color' => ['rgb' => $v['paga'] === $sim ? '15803D' : '94A3B8'],
                ],
            ]);
        }
    }
}
