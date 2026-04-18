<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Machine;
use App\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportSonaeStoresCommand extends Command
{
    protected $signature = 'sonae:import-stores
        {file=/Users/brunocosta/Downloads/Sonae Verification Plan with MAC Address.xlsx : Caminho para o ficheiro Excel}
        {--client=Sonae : Nome do cliente a garantir na tabela clients}
        {--analyze-only : Analisa o ficheiro sem aceder a base de dados}
        {--dry-run : Analisa o ficheiro sem gravar dados}';

    protected $description = 'Importa lojas Sonae e respetivos números de série a partir do plano de verificação Excel.';

    public function handle(): int
    {
        $file = (string) $this->argument('file');
        $clientName = trim((string) $this->option('client')) ?: 'Sonae';
        $analyzeOnly = (bool) $this->option('analyze-only');
        $dryRun = (bool) $this->option('dry-run');

        if (!is_file($file)) {
            $this->error("Ficheiro não encontrado: {$file}");
            return self::FAILURE;
        }

        $rows = $this->loadSpreadsheetRows($file);

        if (empty($rows)) {
            $this->warn('O ficheiro não contém linhas válidas para importar.');
            return self::SUCCESS;
        }

        if ($analyzeOnly) {
            $this->renderAnalysis($rows, $clientName);
            return self::SUCCESS;
        }

        $stats = [
            'stores_created' => 0,
            'stores_updated' => 0,
            'stores_unchanged' => 0,
            'machines_created' => 0,
            'machines_updated' => 0,
            'machines_unchanged' => 0,
            'conflicts' => 0,
        ];

        $conflicts = [];

        $import = function () use ($rows, $clientName, &$stats, &$conflicts): void {
            $this->ensureClient($clientName);

            foreach ($rows as $row) {
                $store = $this->upsertStore($row, $stats);

                foreach ($row['machines'] as $machineRow) {
                    $conflict = $this->upsertMachine($store, $row, $machineRow, $stats);

                    if ($conflict !== null) {
                        $stats['conflicts']++;
                        $conflicts[] = $conflict;
                    }
                }
            }
        };

        if ($dryRun) {
            DB::beginTransaction();

            try {
                $import();
                DB::rollBack();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        } else {
            DB::transaction($import);
        }

        $this->newLine();
        $this->info($dryRun ? 'Analise concluida sem gravar dados.' : 'Importacao concluida com sucesso.');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Lojas criadas', $stats['stores_created']],
                ['Lojas atualizadas', $stats['stores_updated']],
                ['Lojas sem alteracoes', $stats['stores_unchanged']],
                ['Maquinas criadas', $stats['machines_created']],
                ['Maquinas atualizadas', $stats['machines_updated']],
                ['Maquinas sem alteracoes', $stats['machines_unchanged']],
                ['Conflitos', $stats['conflicts']],
            ]
        );

        if (!empty($conflicts)) {
            $this->newLine();
            $this->warn('Foram detetados conflitos de numeros de serie noutras lojas:');
            foreach ($conflicts as $conflict) {
                $this->line(sprintf(
                    '- %s / %s: serie %s ja pertence a loja %s (#%d)',
                    $conflict['store_code'],
                    $conflict['store_name'],
                    $conflict['serial_number'],
                    $conflict['existing_store_code'] ?: 'sem codigo',
                    $conflict['existing_store_id']
                ));
            }
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function renderAnalysis(array $rows, string $clientName): void
    {
        $machineCount = 0;
        $sample = [];

        foreach ($rows as $row) {
            $machineCount += count($row['machines']);

            if (count($sample) < 10) {
                $sample[] = [
                    $row['codigo_loja'],
                    $row['nome_loja'],
                    count($row['machines']),
                ];
            }
        }

        $this->info("Analise do ficheiro para o cliente {$clientName}");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Lojas no ficheiro', count($rows)],
                ['Numeros de serie no ficheiro', $machineCount],
            ]
        );

        $this->newLine();
        $this->table(['Codigo', 'Loja', 'Maquinas'], $sample);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadSpreadsheetRows(string $file): array
    {
        $reader = IOFactory::createReaderForFile($file);
        $reader->setReadDataOnly(true);

        $sheet = $reader->load($file)->getSheet(0);
        $highestColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $headerRowIndex = 4;
        $dataStartRow = 5;

        $headers = [];
        for ($column = 1; $column <= $highestColumnIndex; $column++) {
            $header = (string) $sheet->getCellByColumnAndRow($column, $headerRowIndex)->getFormattedValue();
            $headers[$column] = $this->normalizeHeader($header);
        }

        $stores = [];

        for ($rowIndex = $dataStartRow; $rowIndex <= $sheet->getHighestRow(); $rowIndex++) {
            $row = [];
            $hasData = false;

            for ($column = 1; $column <= $highestColumnIndex; $column++) {
                $value = trim((string) $sheet->getCellByColumnAndRow($column, $rowIndex)->getFormattedValue());
                if ($value !== '') {
                    $hasData = true;
                }

                $row[$headers[$column] ?: "column_{$column}"] = $value;
            }

            if (!$hasData) {
                continue;
            }

            $storeCode = $row['store_code'] ?? '';
            if ($storeCode === '') {
                continue;
            }

            $machines = [];
            foreach ([1, 2] as $slot) {
                $serial = $row["serial_n_machine_{$slot}"] ?? '';
                if ($serial === '') {
                    continue;
                }

                $machines[] = [
                    'serial_number' => $serial,
                    'mac_address' => $row["mac_address_machine_{$slot}"] ?? null,
                    'ip_address' => $this->normalizeIp(
                        $row[$slot === 1 ? 'ip_machine_1_left_side' : 'ip_machine_2_right_side'] ?? null
                    ),
                    'descricao' => $this->buildMachineDescription($row, $slot),
                ];
            }

            $stores[$storeCode] = [
                'client' => $row['client'] ?? 'Sonae',
                'codigo_loja' => $storeCode,
                'nome_loja' => $row['store_name'] ?? $storeCode,
                'morada' => $row['address'] ?? null,
                'codigo_postal' => $row['zip_code'] ?? null,
                'observacoes' => $this->buildStoreNotes($row),
                'machines' => $machines,
            ];
        }

        return array_values($stores);
    }

    private function ensureClient(string $clientName): void
    {
        $client = Client::withTrashed()
            ->where('name', $clientName)
            ->first();

        if ($client === null) {
            $client = new Client();
            $client->name = $clientName;
        }

        if ($client->trashed()) {
            $client->restore();
        }

        if (!$client->exists) {
            $client->save();
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function upsertStore(array $row, array &$stats): Store
    {
        /** @var Store $store */
        $store = Store::withTrashed()->firstOrNew([
            'codigo_loja' => $row['codigo_loja'],
        ]);

        if ($store->trashed()) {
            $store->restore();
        }

        $payload = [
            'regiao' => $store->regiao ?: 'SON',
            'insignia' => 'sonae',
            'codigo_loja' => $row['codigo_loja'],
            'nome_loja' => $row['nome_loja'],
            'morada' => $row['morada'],
            'cidade' => $store->cidade,
            'codigo_postal' => $row['codigo_postal'],
            'contacto_loja' => $store->contacto_loja,
            'telefone' => $store->telefone,
            'email' => $store->email,
            'observacoes' => $this->mergeNotes($store->observacoes, $row['observacoes']),
        ];

        $isNew = !$store->exists;
        $store->fill($payload);

        if ($isNew) {
            $store->save();
            $stats['stores_created']++;
            return $store;
        }

        if ($store->isDirty()) {
            $store->save();
            $stats['stores_updated']++;
        } else {
            $stats['stores_unchanged']++;
        }

        return $store;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $machineRow
     * @return array<string, mixed>|null
     */
    private function upsertMachine(Store $store, array $row, array $machineRow, array &$stats): ?array
    {
        $serialNumber = $machineRow['serial_number'];

        $existingMachine = Machine::query()
            ->where('serial_number', $serialNumber)
            ->first();

        if ($existingMachine !== null && (int) $existingMachine->store_id !== (int) $store->id) {
            return [
                'store_code' => $row['codigo_loja'],
                'store_name' => $row['nome_loja'],
                'serial_number' => $serialNumber,
                'existing_store_id' => (int) $existingMachine->store_id,
                'existing_store_code' => optional($existingMachine->store)->codigo_loja,
            ];
        }

        /** @var Machine $machine */
        $machine = $existingMachine ?? new Machine();

        $isNew = !$machine->exists;
        $machine->fill([
            'store_id' => $store->id,
            'serial_number' => $serialNumber,
            'ip_address' => $machineRow['ip_address'],
            'descricao' => $machineRow['descricao'],
        ]);

        if ($isNew) {
            $machine->save();
            $stats['machines_created']++;
            return null;
        }

        if ($machine->isDirty()) {
            $machine->save();
            $stats['machines_updated']++;
        } else {
            $stats['machines_unchanged']++;
        }

        return null;
    }

    private function normalizeHeader(string $value): string
    {
        $value = str_replace(["\r", "\n"], ' ', trim($value));
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;
        $value = Str::of($value)
            ->ascii()
            ->lower()
            ->replace('/', ' ')
            ->replace('-', ' ')
            ->replace('.', '')
            ->replace('(', ' ')
            ->replace(')', ' ')
            ->replace(':', ' ')
            ->squish()
            ->replace(' ', '_')
            ->value();

        return $value;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function buildStoreNotes(array $row): ?string
    {
        $parts = [
            $this->noteLine('Cliente', $row['client'] ?? null),
            $this->noteLine('Install Date', $row['install_date'] ?? null),
            $this->noteLine('Model', $row['model'] ?? null),
            $this->noteLine('Container Removal', $row['container_removal'] ?? null),
            $this->noteLine('CMR MAN', $row['cmr_man'] ?? null),
            $this->noteLine('Ecospot Build', $row['ecospot_build'] ?? null),
            $this->noteLine('POS in Install Date', $row['pos_in_install_date'] ?? null),
            $this->noteLine('POS after 27/03/2026', $row['pos_after_27_03_2026'] ?? null),
            $this->noteLine('n.machines', $row['nmachines'] ?? null),
        ];

        $notes = implode("\n", array_filter($parts));

        return $notes !== '' ? $notes : null;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function buildMachineDescription(array $row, int $slot): ?string
    {
        $parts = [
            $this->noteLine('Model', $row['model'] ?? null),
            $this->noteLine('MAC', $row["mac_address_machine_{$slot}"] ?? null),
            $this->noteLine('Install Date', $row['install_date'] ?? null),
            $this->noteLine('Ecospot Build', $row['ecospot_build'] ?? null),
            $this->noteLine('POS after 27/03/2026', $row['pos_after_27_03_2026'] ?? null),
        ];

        $description = implode(' | ', array_filter($parts));

        return $description !== '' ? $description : null;
    }

    private function normalizeIp(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '-' || strcasecmp($value, 'dhcp') === 0) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_IP) ? $value : null;
    }

    private function noteLine(string $label, ?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '-') {
            return null;
        }

        return "{$label}: {$value}";
    }

    private function mergeNotes(?string $existing, ?string $incoming): ?string
    {
        $existing = trim((string) $existing);
        $incoming = trim((string) $incoming);

        if ($incoming === '') {
            return $existing !== '' ? $existing : null;
        }

        if ($existing === '') {
            return $incoming;
        }

        if (Str::contains($existing, $incoming)) {
            return $existing;
        }

        return $existing . "\n\n" . $incoming;
    }
}
