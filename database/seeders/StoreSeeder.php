<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Store;

class StoreSeeder extends Seeder
{
    public function run()
    {
        $stores = [
            ['codigo' => '001', 'nome' => 'Loja Central', 'morada' => 'Rua Principal 100'],
            ['codigo' => '002', 'nome' => 'Loja Norte', 'morada' => 'Avenida Norte 200'],
            ['codigo' => '003', 'nome' => 'Loja Sul', 'morada' => 'Travessa Sul 300'],
        ];
        foreach ($stores as $store) {
            Store::firstOrCreate($store);
        }
    }
}
