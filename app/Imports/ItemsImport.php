<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class ItemsImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // Ignorar cabeçalho
        $rows->shift();

        $alemania = Location::where('tipo', 'fornecedor')->first();
        $armazem  = Location::where('tipo', 'armazem')->first();

        if (!$alemania || !$armazem) {
            throw new \Exception("⚠️ Precisas de ter pelo menos 1 fornecedor e 1 armazém registados em locations antes da importação.");
        }

        foreach ($rows as $row) {
            $referencia = trim($row[0]); // Coluna "Peça"
            $nome = trim($row[1]);       // Coluna "Designação 1"
            $quantidade = intval($row[2]); // Coluna "Existência"

            if (!$referencia || !$quantidade) continue;

            // Criar item se não existir
            $item = Item::firstOrCreate(
                ['referencia' => $referencia],
                ['nome' => $nome]
            );

            // Criar movimento de stock (Alemanha → Armazém)
            StockMovement::create([
                'item_id' => $item->id,
                'quantidade' => $quantidade,
                'origem_id' => $alemania->id,
                'destino_id' => $armazem->id,
                'tipo_movimento' => 'entrada',
                'observacoes' => 'Importação inicial de Excel',
                'user_id' => Auth::id() ?? 1, // fallback para admin
            ]);
        }
    }
}
