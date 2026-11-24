<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Meses contratados das manutenções Lidl, do ficheiro "Lidl Plano de Manutenção Update".
 *
 * O ficheiro é por máquina (número de série + mês); aqui já vem por loja. Guarda-se o
 * dia 1 do mês, no próximo que ainda está para vir (novembro e dezembro em 2026, o resto
 * em 2027). Só preenche lojas sem manutenção registada: o que já lá está não muda.
 *
 * Como agora todas as manutenções são contratadas ao mês, as datas que já existiam
 * passam também para o dia 1 do seu mês.
 */
return new class extends Migration
{
    /** Código da loja => mês contratado (ano-mês). */
    private const PLANO = [
        '114' => '2027-01', // Abrantes
        '117' => '2027-01', // Castelo Branco
        '119' => '2027-03', // Amadora - Venteira
        '129' => '2027-06', // Tapada das Mercês
        '137' => '2026-11', // Alcobaça
        '139' => '2027-02', // Rio Maior
        '142' => '2027-06', // Povoa de Santa Iria
        '155' => '2027-10', // Lisboa - Carnide
        '157' => '2027-02', // Salvaterra de Magos
        '158' => '2027-03', // Sassoeiros
        '168' => '2027-02', // Santarém-Av. Bernardo Santareno
        '176' => '2026-11', // Caldas da Rainha
        '178' => '2027-07', // Malveira
        '181' => '2027-07', // Ericeira
        '185' => '2026-11', // Agualva Cacém
        '189' => '2027-04', // Lisboa - Alvaláxia
        '190' => '2027-04', // Av. Lusíada
        '214' => '2027-06', // Santo Tirso
        '230' => '2027-04', // Paredes (sem insígnia)
        '241' => '2027-09', // Guarda
        '247' => '2027-09', // Mangualde
        '249' => '2027-07', // Vila do Conde (sem insígnia)
        '263' => '2027-01', // Valença
        '267' => '2027-01', // Esposende
        '268' => '2027-02', // Fafe
        '277' => '2027-03', // Matosinhos - Leça da Palmeira
        '281' => '2027-08', // Sever do Vouga
        '282' => '2027-02', // Póvoa do Lanhoso
        '285' => '2027-07', // Mirandela
        '288' => '2027-01', // Barcelos
        '291' => '2027-07', // Macedo de Cavaleiros
        '340' => '2026-12', // Almada Feijó
        '342' => '2027-09', // Setúbal S. Sebastião (sem insígnia)
        '346' => '2026-11', // Odemira
        '352' => '2026-12', // Alcochete
        '354' => '2027-10', // Alcácer
        '356' => '2027-09', // Pinhal Novo
        '357' => '2027-05', // Albufeira
        '360' => '2027-08', // Almada-Sobreda
        '362' => '2027-09', // Seixal Miratejo (sem insígnia)
        '364' => '2027-08', // Azeitão (sem insígnia)
        '369' => '2027-08', // Grândola
        '370' => '2027-10', // Setúbal-Estr. de Algeruz
        '371' => '2027-09', // Santiago do Cacém (sem insígnia)
        '403' => '2027-10', // Miranda do Corvo
        '406' => '2027-09', // Santa Comba Dão
        '409' => '2026-11', // Alcobaça - Benedita
        '410' => '2027-08', // Oliveira do Bairro
        '411' => '2027-02', // Ourém
        '412' => '2026-11', // Penacova
        '414' => '2027-01', // Entroncamento
        '418' => '2027-08', // São joão da Madeira
        '419' => '2027-06', // Mafamude
        '422' => '2027-07', // Peso da Régua
        '423' => '2027-05', // Porto - Camilo
        '428' => '2027-02', // Pevidém
        '430' => '2026-11', // Soure
        '431' => '2027-03', // Maia Vermoim
        '435' => '2027-06', // Loures Moscavide
        '437' => '2027-01', // Fundão
        '444' => '2027-02', // Alenquer
        '446' => '2027-05', // Póvoa do Varzim
        '448' => '2027-03', // Cascais - Torre
        '451' => '2027-06', // Gondomar - S. Cosme
        '453' => '2027-04', // Matosinhos Sul
        '463' => '2027-05', // Silves (sem insígnia)
        '467' => '2027-10', // Figueira da Foz (sem insígnia)
        '472' => '2027-06', // Sacavém
        '475' => '2027-03', // Vila Verde - Rua dos Bombeiros
        '482' => '2027-05', // Porto - Diogo Botelho
        '483' => '2027-03', // Sintra - Casal de Cambra
        '486' => '2027-04', // Casal Ribeiro (sem insígnia)
        '490' => '2027-08', // Barreiro - Quinta das Canas
        '494' => '2027-06', // Gondomar Rio Tinto
        '497' => '2027-08', // Seixal Fernão Ferro
        '500' => '2027-05', // Fernão Magalhães
        '502' => '2027-04', // Loures Frielas (sem insígnia)
        '505' => '2027-02', // Vila Nova de Famalicão - Pousada de Saramagos
        '510' => '2027-10', // Lousã
        '524' => '2027-01', // Braga - Imaculada Conceição (sem insígnia)
        '525' => '2026-12', // Vila Franca de Xira
        '534' => '2027-09', // Arganil
        '539' => '2027-07', // Massamá Norte
        '541' => '2027-05', // Armação de Pêra (sem insígnia)
        '545' => '2027-01', // Cartaxo
        '549' => '2027-04', // Porto AEP
        '552' => '2027-03', // Amadora - Venda Nova
        '562' => '2027-03', // Sintra - Mem Martins
        '565' => '2027-09', // Viseu Sátão (sem insígnia)
        '569' => '2027-06', // Oeiras - Área de Serviço
        '571' => '2027-10', // Oliveira do Hospital
        '576' => '2027-08', // São João Madeira - Oliveira Junior Arrifana
        '584' => '2027-01', // Arcos de Veldevez
        '585' => '2027-07', // Albergaria
        '588' => '2026-12', // Vieira de Leiria
        '595' => '2027-02', // Santarém
        '599' => '2027-09', // Vale de Cambra
        '608' => '2026-12', // Leiria - Paulo VI
        '610' => '2027-05', // Mafra
        '612' => '2027-05', // Faro (sem insígnia)
        '618' => '2026-12', // Sintra Cacém
        '619' => '2027-04', // Matosinhos Perafita
        '620' => '2027-01', // Alcains
        '636' => '2027-07', // Aveiro Verdemilho
    ];

    public function up(): void
    {
        $agora = now();

        foreach (self::PLANO as $codigo => $mes) {
            $lojas = DB::table('stores')->whereNull('deleted_at')->where('codigo_loja', (string) $codigo)->pluck('id');

            // Código repetido em duas lojas: não se adivinha qual é, fica para escolher à mão
            if ($lojas->count() !== 1 || DB::table('store_maintenances')->whereIn('store_id', $lojas)->exists()) {
                continue;
            }

            DB::table('store_maintenances')->insert([
                'store_id' => $lojas->first(),
                'data_contratada' => $mes . '-01',
                'ativo' => 1,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);
        }

        DB::table('store_maintenances')
            ->whereRaw('DAY(data_contratada) <> 1')
            ->update(['data_contratada' => DB::raw("DATE_FORMAT(data_contratada, '%Y-%m-01')")]);
    }

    public function down(): void
    {
        // Migração de dados: não se desfaz, para não apagar manutenções entretanto usadas.
    }
};
