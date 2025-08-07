<?php
namespace App\Imports;

use App\Models\Item;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;

class ItemImport implements OnEachRow, WithStartRow
{
    protected $batchId;

    public function __construct($batchId = null)
    {
        $this->batchId = $batchId ?? uniqid('import_', true);
    }
    public function startRow(): int
    {
        return 3; // Porque os cabeçalhos reais estão na linha 2, e os dados começam na linha 3
    }

    public function onRow(Row $row)
    {
        $rowArray = $row->toArray();

        // Verifica se os índices 0 (Parte), 1 (Designação) e 3 (Transferência do Técnico Portugal) existem
        if (!empty($rowArray[0]) && !empty($rowArray[1]) && isset($rowArray[3])) {
            $item = Item::where('referencia', $rowArray[0])->first();
            $quantidade = (int) $rowArray[3];
            if ($item) {
                // Atualiza o stock (quantidade_atual) somando o valor importado
                $item->quantidade_atual += $quantidade;
                $item->save();
            } else {
                // Cria novo item
                $item = Item::create([
                    'referencia'       => $rowArray[0], // Parte
                    'nome'             => $rowArray[1], // Designação
                    'quantidade_atual' => $quantidade, // Transferência do Técnico Portugal
                    'tipo'             => 'peça',
                    'unidade_medida'   => 'un',
                ]);
            }
            // Registra movimento de stock (entrada) com batch
            StockMovement::create([
                'item_id'      => $item->id,
                'tipo'         => 'entrada',
                'quantidade'   => $quantidade,
                'motivo'       => 'Importação Excel',
                'origem'       => 'importacao',
                'user_id'      => Auth::id() ?? 1, // fallback para 1 se não houver user logado
                'import_batch' => $this->batchId,
            ]);
        }
    }
}
