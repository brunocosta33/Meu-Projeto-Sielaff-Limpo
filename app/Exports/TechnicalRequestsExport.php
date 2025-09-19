<?php

namespace App\Exports;

use App\Models\TechnicalRequest;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TechnicalRequestsExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    public function collection()
    {
        return TechnicalRequest::with('store')
            ->get()
            ->map(function ($req) {
                return [
                    'ID'               => $req->id,
                    'Loja'             => $req->store->codigo_loja . ' - ' . $req->store->nome_loja,
                    'Estado'           => ucfirst($req->estado),
                    'Origem'           => $req->origem,
                    'Tipo de Serviço'  => $req->tipo_servico,
                    'Descrição'        => $req->descricao_problema,
                    'Prioridade'       => ucfirst($req->prioridade),
                    'Data Pedido'      => $req->data_pedido ? Carbon::parse($req->data_pedido)->format('d/m/Y') : '',
                    'Data Agendamento' => $req->data_agendamento ? Carbon::parse($req->data_agendamento)->format('d/m/Y H:i') : '',
                    'Data Resolução'   => $req->data_resolucao ? Carbon::parse($req->data_resolucao)->format('d/m/Y') : '',
                    'Observações'      => $req->observacoes,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Loja',
            'Estado',
            'Origem',
            'Tipo de Serviço',
            'Descrição',
            'Prioridade',
            'Data Pedido',
            'Data Agendamento',
            'Data Resolução',
            'Observações',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Cabeçalho (linha 1)
        $sheet->getStyle(1)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'], // texto branco
                'size' => 12,
            ],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => '4CAF50'], // verde
            ],
            'alignment' => [
                'horizontal' => 'center',
            ],
        ]);

        // Largura automática
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowCount = $sheet->getHighestRow();

                // Iterar pelas linhas (começa em 2 porque 1 é cabeçalho)
                for ($row = 2; $row <= $rowCount; $row++) {
                    $estado = strtolower($sheet->getCell("C$row")->getValue()); // Estado está na coluna C
                    $prioridade = strtolower($sheet->getCell("G$row")->getValue()); // Prioridade está na coluna G
                    $dataAgendamento = $sheet->getCell("I$row")->getValue(); // Data Agendamento na coluna I
                    $dataResolucao   = $sheet->getCell("J$row")->getValue(); // Data Resolução na coluna J

                    // --- Cores para ESTADO ---
                    switch ($estado) {
                        case 'pendente':
                            $colorEstado = 'FFF59D'; // amarelo claro
                            break;
                        case 'agendado':
                            $colorEstado = '81D4FA'; // azul claro
                            break;
                        case 'concluido':
                            $colorEstado = 'A5D6A7'; // verde claro
                            break;
                        case 'cancelado':
                            $colorEstado = 'EF9A9A'; // vermelho claro
                            break;
                        case 'aguarda peca':
                            $colorEstado = 'FF7043'; // laranja
                            break;
                        default:
                            $colorEstado = null;
                    }

                    if ($colorEstado) {
                        $sheet->getStyle("C$row")->applyFromArray([
                            'fill' => [
                                'fillType' => 'solid',
                                'color' => ['rgb' => $colorEstado],
                            ],
                        ]);
                    }

                    // --- Cores para PRIORIDADE ---
                    switch ($prioridade) {
                        case 'baixa':
                            $colorPrioridade = '81D4FA'; // azul claro
                            break;
                        case 'media':
                            $colorPrioridade = 'FFF59D'; // amarelo claro
                            break;
                        case 'alta':
                            $colorPrioridade = 'EF9A9A'; // vermelho claro
                            break;
                        default:
                            $colorPrioridade = null;
                    }

                    if ($colorPrioridade) {
                        $sheet->getStyle("G$row")->applyFromArray([
                            'fill' => [
                                'fillType' => 'solid',
                                'color' => ['rgb' => $colorPrioridade],
                            ],
                        ]);
                    }

                    // --- Cor para Data Agendamento (coluna I) ---
                    if (!empty($dataAgendamento)) {
                        $sheet->getStyle("I$row")->applyFromArray([
                            'fill' => [
                                'fillType' => 'solid',
                                'color' => ['rgb' => 'FFF59D'], // amarelo
                            ],
                        ]);
                    }

                    // --- Cor para Data Resolução (coluna J) ---
                    if (!empty($dataResolucao)) {
                        $sheet->getStyle("J$row")->applyFromArray([
                            'fill' => [
                                'fillType' => 'solid',
                                'color' => ['rgb' => 'A5D6A7'], // verde
                            ],
                        ]);
                    }
                }
            },
        ];
    }
}
