<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ItemImport;
use App\Models\Item;


class StockImportController extends Controller
{
    public function showForm()
    {
        return view('backoffice.stock.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'ficheiro' => 'required|file|mimes:xlsx,xls'
        ]);

        $batchId = uniqid('import_', true);
        Excel::import(new ItemImport($batchId), $request->file('ficheiro'));

        return redirect()->back()->with('success', 'Importação concluída com sucesso.');
    }
}
