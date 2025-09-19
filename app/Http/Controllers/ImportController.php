<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Imports\ItemsImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function showForm()
    {
        return view('backoffice.imports.items');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        Excel::import(new ItemsImport, $request->file('file'));

        return redirect()->back()->with('success','Peças importadas com sucesso!');
    }
}
