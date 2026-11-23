<?php

namespace App\Exports;

use App\Exports\Concerns\FolhaComEstilo;
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

/** Manutenções anuais em Excel, com os mesmos filtros que estão à vista na página. */
class StoreMaintenancesExport implements FromCollection, WithHeadings, WithEvents, WithTitle, WithColumnWidths, WithStrictNullComparison, ShouldAutoSize
{
    use FolhaComEstilo;

    //  A Cliente  E Cidade        I Prazo    M Técnico       Q Fotografias
    //  B Código   F Modelos       J Mês      N Observações
    //  C Loja     G Nºs de série  K Estado   O Notas
    //  D Zona     H Contratada    L Feita em P Folhas
    public function __construct(private $linhas, private int $ano)
    {
    }

    protected function colunasCentradas(): array
    {
        return ['A', 'B', 'D', 'H', 'I', 'J', 'K', 'L', 'P', 'Q'];
    }

    protected function colunasLargas(): array
    {
        return ['C', 'G', 'N', 'O'];
    }

    public function title(): string
    {
        return __('Manutenções') . ' ' . $this->ano;
    }

    public function columnWidths(): array
    {
        return ['A' => 10, 'B' => 11, 'C' => 30, 'D' => 10, 'E' => 16, 'F' => 18, 'G' => 24,
            'H' => 14, 'I' => 18, 'J' => 12, 'K' => 19, 'L' => 12, 'M' => 18, 'N' => 28, 'O' => 24,
            'P' => 9, 'Q' => 11];
    }

    public function collection()
    {
        $estados = config('maintenance.states');

        return collect($this->linhas)->map(function ($r) use ($estados) {
            $m = $r->maintenance;
            $registo = $r->record;
            $mensal = $m && $m->isMensal();

            return [
                $r->store->clienteConfig()['label'] ?? '',
                $r->store->codigo_loja,
                $r->store->nome_loja,
                $r->store->regiao,
                $r->store->cidade,
                $r->store->machines->pluck('modelo')->filter()->unique()->implode(', '),
                $r->store->machines->pluck('serial_number')->filter()->implode(', '),
                $m ? $m->data_contratada->format('d/m/Y') : '',
                $r->start && $r->end
                    ? ($mensal ? __('o mês inteiro') : $r->start->format('d/m') . ' – ' . $r->end->format('d/m'))
                    : '',
                $r->due ? ucfirst($r->due->translatedFormat('F')) : '',
                __($estados[$r->status]['label'] ?? $r->status),
                $registo?->data_realizada?->format('d/m/Y') ?? '',
                $registo?->technician->name ?? '',
                $registo?->observacoes ?? '',
                $m?->observacoes ?? '',
                $registo?->folhas->count() ?? 0,
                $registo?->fotos->count() ?? 0,
            ];
        });
    }

    public function headings(): array
    {
        return [
            __('Cliente'), __('Código'), __('Loja'), __('Zona'), __('Cidade'),
            __('Modelos'), __('Nºs de série'),
            __('Data contratada'), __('Prazo'), __('Mês'), __('Estado'),
            __('Feita em'), __('Técnico'), __('Observações da manutenção'), __('Notas do contrato'),
            __('Folhas'), __('Fotografias'),
        ];
    }

    /** O estado leva a mesma cor que tem na página. */
    protected function estiloExtra(Worksheet $folha, int $ultimaLinha, string $ultimaColuna): void
    {
        $cores = [];
        foreach (config('maintenance.states') as $estado) {
            $cores[__($estado['label'])] = [
                ltrim($estado['bg'], '#'),
                ltrim($estado['color'], '#'),
            ];
        }

        $valores = [];
        for ($linha = 2; $linha <= $ultimaLinha; $linha++) {
            $valores[$linha] = (string) $folha->getCell('K' . $linha)->getValue();
        }

        foreach ($valores as $linha => $estado) {
            if (!isset($cores[$estado])) {
                continue;
            }

            [$fundo, $letra] = $cores[$estado];
            $folha->getStyle('K' . $linha)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => strtoupper($letra)]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => strtoupper($fundo)]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'indent' => 0],
            ]);
        }
    }
}
