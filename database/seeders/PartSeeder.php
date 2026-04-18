<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Part;

class PartSeeder extends Seeder
{
    public function run()
    {
        $parts = [
            ['referencia' => 'P001', 'nome' => 'Filtro de Água'],
            ['referencia' => 'P002', 'nome' => 'Bomba de Café'],
            ['referencia' => 'P003', 'nome' => 'Painel de Controlo'],
        ];
        foreach ($parts as $part) {
            Part::firstOrCreate($part);
        }
    }
}
