<?php

namespace App\Exports;

use App\Exports\Concerns\FolhaComEstilo;
use App\Models\ToolDelivery;
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

/** Entregas em Excel, com os mesmos filtros que estão à vista. */
class ToolDeliveriesExport implements FromCollection, WithHeadings, WithEvents, WithTitle, WithColumnWidths, WithStrictNullComparison, ShouldAutoSize
{
    use FolhaComEstilo;

    //  A Data   C Tipo     E Código   G Quantidade  I Lançado em   K Observações
    //  B Hora   D Técnico  F Artigo   H No ProAlpha J Lançado por  L Registado por
    protected function colunasCentradas(): array
    {
        return ['A', 'B', 'C', 'G', 'H', 'I'];
    }

    protected function colunasLargas(): array
    {
        return ['F', 'K'];
    }

    public function __construct(private $query)
    {
    }

    public function title(): string
    {
        return __('Entregas');
    }

    public function columnWidths(): array
    {
        return ['A' => 12, 'B' => 8, 'C' => 13, 'E' => 16, 'F' => 34, 'G' => 11, 'H' => 13, 'I' => 16, 'K' => 32];
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

    /** O que falta lançar no ProAlpha salta à vista, tal como na página. */
    protected function estiloExtra(Worksheet $folha, int $ultimaLinha, string $ultimaColuna): void
    {
        $folha->getStyle('E2:E' . $ultimaLinha)->applyFromArray([
            'font' => ['name' => 'Consolas', 'bold' => true],
        ]);

        $sim = __('Sim');

        // Ler tudo primeiro: os estilos são partilhados e ler no meio de escritas
        // faz a cor de uma célula saltar para outra
        $lancado = [];
        for ($linha = 2; $linha <= $ultimaLinha; $linha++) {
            $lancado[$linha] = (string) $folha->getCell('H' . $linha)->getValue() === $sim;
        }

        foreach ($lancado as $linha => $sim_) {
            $folha->getStyle('H' . $linha)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $sim_ ? '15803D' : 'C2410C']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $sim_ ? 'DCFCE7' : 'FFF7ED']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'indent' => 0],
            ]);
        }
    }
}
