<?php

namespace App\Exports\Concerns;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * O aspeto comum a todas as folhas que saem do backoffice: cabeçalho azul, riscas,
 * filtro automático, primeira linha congelada e pronta a imprimir.
 *
 * Quem usa a trait declara o que tem de especial nas propriedades abaixo
 * (colunas de dinheiro, colunas ao centro, colunas de texto largo).
 */
trait FolhaComEstilo
{
    /** Azul do backoffice, para o cabeçalho. */
    private const COR_CABECALHO = '0F4C81';
    private const COR_RISCA = 'F6F8FB';
    private const COR_GRELHA = 'E2E8F0';

    /** Colunas com valores em euros: ficam com formato de moeda e somatório no fim. */
    protected function colunasDinheiro(): array
    {
        return [];
    }

    /** Colunas curtas (datas, horas, contagens) que ficam melhor ao centro. */
    protected function colunasCentradas(): array
    {
        return [];
    }

    /** Colunas de texto corrido: largura fixa e mudança de linha, para não esticarem a folha. */
    protected function colunasLargas(): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $folha = $event->sheet->getDelegate();
                $ultimaColuna = $folha->getHighestColumn();
                $ultimaLinha = $folha->getHighestRow();

                $this->cabecalho($folha, $ultimaColuna);
                $folha->freezePane('A2');
                $folha->setAutoFilter('A1:' . $ultimaColuna . max($ultimaLinha, 1));

                if ($ultimaLinha > 1) {
                    $this->corpo($folha, $ultimaColuna, $ultimaLinha);
                    $this->somatorio($folha, $ultimaColuna, $ultimaLinha);
                    $this->estiloExtra($folha, $ultimaLinha, $ultimaColuna);
                }

                $this->impressao($folha, $ultimaColuna);
            },
        ];
    }

    /** Gancho para cada folha pôr o que só a ela diz respeito. Por omissão não faz nada. */
    protected function estiloExtra(Worksheet $folha, int $ultimaLinha, string $ultimaColuna): void
    {
    }

    private function cabecalho(Worksheet $folha, string $ultimaColuna): void
    {
        $folha->getRowDimension(1)->setRowHeight(32);
        $folha->getStyle('A1:' . $ultimaColuna . '1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COR_CABECALHO]],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'wrapText' => true,
                'indent' => 1,
            ],
        ]);
    }

    private function corpo(Worksheet $folha, string $ultimaColuna, int $ultimaLinha): void
    {
        $folha->getStyle('A2:' . $ultimaColuna . $ultimaLinha)->applyFromArray([
            'font' => ['size' => 10, 'color' => ['rgb' => '1F2937']],
            'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'indent' => 1],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COR_GRELHA]]],
        ]);

        // Riscas alternadas: dá para seguir a linha com os olhos numa folha larga
        for ($linha = 3; $linha <= $ultimaLinha; $linha += 2) {
            $folha->getStyle('A' . $linha . ':' . $ultimaColuna . $linha)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COR_RISCA]],
            ]);
        }

        foreach ($this->colunasCentradas() as $coluna) {
            $folha->getStyle($coluna . '2:' . $coluna . $ultimaLinha)->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'indent' => 0],
            ]);
        }

        foreach ($this->colunasLargas() as $coluna) {
            $folha->getStyle($coluna . '2:' . $coluna . $ultimaLinha)->applyFromArray([
                'alignment' => ['wrapText' => true],
            ]);
        }

        foreach ($this->colunasDinheiro() as $coluna) {
            $folha->getStyle($coluna . '2:' . $coluna . $ultimaLinha)->applyFromArray([
                'numberFormat' => ['formatCode' => '#,##0.00 "€"'],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ]);
        }
    }

    /** Linha de totais no fim. SUBTOTAL e não SOMA: acompanha o filtro de quem está a ver. */
    private function somatorio(Worksheet $folha, string $ultimaColuna, int $ultimaLinha): void
    {
        if (!$this->colunasDinheiro()) {
            return;
        }

        $total = $ultimaLinha + 1;
        $folha->setCellValue('A' . $total, __('Total'));

        foreach ($this->colunasDinheiro() as $coluna) {
            $folha->setCellValue($coluna . $total, '=SUBTOTAL(109,' . $coluna . '2:' . $coluna . $ultimaLinha . ')');
            $folha->getStyle($coluna . $total)->applyFromArray([
                'numberFormat' => ['formatCode' => '#,##0.00 "€"'],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ]);
        }

        $folha->getStyle('A' . $total . ':' . $ultimaColuna . $total)->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['indent' => 1],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => self::COR_CABECALHO]]],
        ]);
    }

    private function impressao(Worksheet $folha, string $ultimaColuna): void
    {
        $folha->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $folha->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);
        $folha->getPageMargins()->setTop(0.5)->setBottom(0.5)->setLeft(0.3)->setRight(0.3);
        $folha->getHeaderFooter()->setOddFooter('&L&"Calibri"&9' . $folha->getTitle() . '&R&"Calibri"&9&P / &N');
    }
}
