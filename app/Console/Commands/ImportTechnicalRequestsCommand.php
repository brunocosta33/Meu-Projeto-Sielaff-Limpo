<?php

namespace App\Console\Commands;

use App\Models\Machine;
use App\Models\Store;
use App\Models\TechnicalRequest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportTechnicalRequestsCommand extends Command
{
    private const ZONES = [
        'norte' => 'norte',
        'centro' => 'centro',
        'sul' => 'sul',
    ];

    protected $signature = 'technical-requests:import
        {file=/Users/brunocosta/Downloads/pedidos_assistencia.xlsx : Caminho do Excel/CSV a importar}
        {--sheet= : Nome da folha a usar}
        {--analyze-only : Analisa o ficheiro sem aceder a base de dados}
        {--dry-run : Simula a importação sem gravar}
        {--default-origin=HotLine : Origem por defeito quando a coluna vier vazia}';

    protected $description = 'Importa pedidos de assistência técnica a partir de Excel/CSV.';

    public function handle(): int
    {
        $file = (string) $this->argument('file');
        $sheetName = $this->option('sheet');
        $analyzeOnly = (bool) $this->option('analyze-only');
        $dryRun = (bool) $this->option('dry-run');
        $defaultOrigin = trim((string) $this->option('default-origin')) ?: 'HotLine';

        if (!is_file($file)) {
            $this->error("Ficheiro não encontrado: {$file}");
            return self::FAILURE;
        }

        $rows = $this->loadRows($file, $sheetName);

        if (empty($rows)) {
            $this->warn('Não encontrei linhas válidas para importar.');
            return self::SUCCESS;
        }

        if ($analyzeOnly) {
            $this->renderAnalysis($rows);
            return self::SUCCESS;
        }

        $stats = [
            'created' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        $skipped = [];

        $import = function () use ($rows, $defaultOrigin, &$stats, &$skipped): void {
            foreach ($rows as $index => $row) {
                try {
                    $resolved = $this->resolveRow($row, $defaultOrigin);

                    if ($resolved['skip']) {
                        $stats['skipped']++;
                        $skipped[] = 'Linha ' . $row['_row'] . ': ' . $resolved['reason'];
                        continue;
                    }

                    $request = $this->findExistingRequest($resolved['payload']);
                    $isNew = $request === null;
                    $request = $request ?? new TechnicalRequest();

                    $request->fill($resolved['payload']);
                    $request->created_by = $resolved['created_by'];
                    $request->updated_by = $resolved['updated_by'];
                    $request->assigned_technician_id = $resolved['assigned_technician_id'];

                    if ($resolved['created_at']) {
                        $request->created_at = $resolved['created_at'];
                    }

                    if ($resolved['updated_at']) {
                        $request->updated_at = $resolved['updated_at'];
                    }

                    if ($isNew) {
                        $request->save();
                        $stats['created']++;
                        continue;
                    }

                    if ($request->isDirty()) {
                        $request->save();
                        $stats['updated']++;
                    } else {
                        $stats['unchanged']++;
                    }
                } catch (\Throwable $e) {
                    $stats['errors']++;
                    $skipped[] = 'Linha ' . ($row['_row'] ?? ($index + 1)) . ': ' . $e->getMessage();
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

        $this->info($dryRun ? 'Simulação concluída.' : 'Importação concluída.');
        $this->table(['Métrica', 'Total'], [
            ['Criados', $stats['created']],
            ['Atualizados', $stats['updated']],
            ['Sem alteração', $stats['unchanged']],
            ['Ignorados', $stats['skipped']],
            ['Erros', $stats['errors']],
        ]);

        if (!empty($skipped)) {
            $this->newLine();
            $this->warn('Linhas ignoradas / com erro:');
            foreach (array_slice($skipped, 0, 50) as $line) {
                $this->line('- ' . $line);
            }
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadRows(string $file, ?string $sheetName = null): array
    {
        $reader = IOFactory::createReaderForFile($file);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $sheet = $sheetName
            ? $spreadsheet->getSheetByName($sheetName)
            : $spreadsheet->getSheet(0);

        if (!$sheet) {
            throw new \RuntimeException('Folha não encontrada no ficheiro.');
        }

        $highestColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $headerRow = 1;
        $headers = [];

        for ($column = 1; $column <= $highestColumnIndex; $column++) {
            $headers[$column] = $this->normalizeHeader((string) $sheet->getCellByColumnAndRow($column, $headerRow)->getFormattedValue());
        }

        $rows = [];

        for ($row = 2; $row <= $sheet->getHighestRow(); $row++) {
            $current = ['_row' => $row];
            $hasData = false;

            for ($column = 1; $column <= $highestColumnIndex; $column++) {
                $key = $headers[$column] ?: 'column_' . $column;
                $value = trim((string) $sheet->getCellByColumnAndRow($column, $row)->getFormattedValue());
                $current[$key] = $value;

                if ($value !== '') {
                    $hasData = true;
                }
            }

            if (!$hasData) {
                continue;
            }

            $rows[] = $current;
        }

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function renderAnalysis(array $rows): void
    {
        $this->info('Análise do ficheiro de pedidos.');
        $this->table(['Métrica', 'Total'], [
            ['Linhas', count($rows)],
            ['Com loja', collect($rows)->filter(fn ($row) => $this->value($row, ['n_de_loja', 'loja']) !== null)->count()],
            ['Com série', collect($rows)->filter(fn ($row) => $this->value($row, ['n_de_serie']) !== null)->count()],
            ['Com status', collect($rows)->filter(fn ($row) => $this->value($row, ['status', 'estado']) !== null)->count()],
        ]);

        foreach (array_slice($rows, 0, 5) as $row) {
            $this->line(sprintf(
                '- Linha %s | loja=%s | serie=%s | estado=%s | descricao=%s',
                $row['_row'],
                $this->value($row, ['n_de_loja', 'loja']) ?? '-',
                $this->value($row, ['n_de_serie']) ?? '-',
                $this->value($row, ['status', 'estado']) ?? '-',
                Str::limit((string) ($this->value($row, ['tipo_de_avaria', 'descricao']) ?? ''), 60)
            ));
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function resolveRow(array $row, string $defaultOrigin): array
    {
        $storeCodeRaw = $this->value($row, ['n_de_loja', 'loja']);
        $storeNameRaw = $this->value($row, ['nome_da_loja', 'loja']);
        $serialNumber = $this->clean($this->value($row, ['n_de_serie']));
        $description = $this->clean($this->value($row, ['tipo_de_avaria', 'descricao'])) ?? '';
        $origin = $this->clean($this->value($row, ['origem_do_pedido', 'origem'])) ?? $defaultOrigin;

        if (!$storeCodeRaw && !$storeNameRaw && !$serialNumber && $description === '') {
            return ['skip' => true, 'reason' => 'Linha vazia ou sem dados úteis.'];
        }

        $store = $this->resolveStore($storeCodeRaw, $storeNameRaw);
        if (!$store) {
            return ['skip' => true, 'reason' => 'Loja não encontrada.'];
        }

        $machine = $this->resolveMachine($store, $serialNumber);

        $status = $this->mapStatus($this->value($row, ['status', 'estado']));
        $priority = $this->mapPriority($this->value($row, ['prioridade']));
        $serviceType = $this->mapServiceType($this->value($row, ['tipo_de_servico'])) ?? 'reparacao';

        $dataPedido = $this->parseDate($this->value($row, ['data_do_pedido', 'data_pedido']));
        if (!$dataPedido) {
            return ['skip' => true, 'reason' => 'Data do pedido inválida.'];
        }

        $horaPedido = $this->parseTime($this->value($row, ['hora_do_pedido']));
        $createdAt = $horaPedido
            ? $dataPedido->copy()->setTimeFrom($horaPedido)
            : $dataPedido->copy()->startOfDay();

        $dataConclusao = $this->parseDate($this->value($row, ['data_da_conclusao', 'data_resolucao']));
        $horaConclusao = $this->parseTime($this->value($row, ['hora_da_conclusao']));
        $dataResolucao = $dataConclusao
            ? ($horaConclusao ? $dataConclusao->copy()->setTimeFrom($horaConclusao) : $dataConclusao->copy()->endOfDay())
            : null;

        $nomeHotline = $this->clean($this->value($row, ['nome_hotline']));
        $pedidoAtribuido = $this->clean($this->value($row, ['pedido_atribuido', 'resolvido_por']));
        $cliente = $this->clean($this->value($row, ['cliente']));
        $contacto = $this->clean($this->value($row, ['contacto']));
        $telefone = $this->clean($this->value($row, ['telf', 'telefone']));
        $area = $this->clean($this->value($row, ['area']));
        $zona = $this->mapZone($this->value($row, ['zona']));

        $observacoes = $this->mergeNotes([
            $this->clean($this->value($row, ['observacoes'])),
            $cliente ? 'Cliente: ' . $cliente : null,
            $contacto ? 'Contacto: ' . $contacto : null,
            $telefone ? 'Telefone: ' . $telefone : null,
            $area ? 'Área: ' . $area : null,
            $nomeHotline && !$this->resolveUser($nomeHotline) ? 'Nome Hotline: ' . $nomeHotline : null,
            $pedidoAtribuido && !$this->resolveUser($pedidoAtribuido) ? 'Pedido atribuído: ' . $pedidoAtribuido : null,
        ]);

        $createdBy = $this->resolveUser($nomeHotline)?->id;
        $assignedTechnicianId = $this->resolveUser($pedidoAtribuido)?->id;

        $payload = [
            'store_id' => $store->id,
            'machine_id' => $machine?->id,
            'origem' => $origin,
            'tipo_servico' => $serviceType,
            'descricao_problema' => $description,
            'prioridade' => $priority,
            'estado' => $status,
            'observacoes' => $observacoes,
            'data_pedido' => $createdAt->toDateString(),
            'data_agendamento' => null,
            'data_resolucao' => $dataResolucao?->toDateTimeString(),
        ];

        if (Schema::hasColumn('technical_requests', 'zona')) {
            $payload['zona'] = $zona;
        }

        return [
            'skip' => false,
            'payload' => $payload,
            'created_by' => $createdBy,
            'updated_by' => $createdBy,
            'assigned_technician_id' => $assignedTechnicianId,
            'created_at' => $createdAt->toDateTimeString(),
            'updated_at' => ($dataResolucao ?? $createdAt)->toDateTimeString(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function findExistingRequest(array $payload): ?TechnicalRequest
    {
        return TechnicalRequest::query()
            ->where('store_id', $payload['store_id'])
            ->where('origem', $payload['origem'])
            ->whereDate('data_pedido', $payload['data_pedido'])
            ->where('descricao_problema', $payload['descricao_problema'])
            ->when($payload['machine_id'], function ($query) use ($payload) {
                $query->where('machine_id', $payload['machine_id']);
            }, function ($query) {
                $query->whereNull('machine_id');
            })
            ->first();
    }

    private function resolveStore(?string $storeCodeRaw, ?string $storeNameRaw): ?Store
    {
        $storeCode = $this->extractStoreCode($storeCodeRaw);
        $storeName = $this->cleanStoreName($storeNameRaw ?: $storeCodeRaw);

        if ($storeCode) {
            $store = Store::query()
                ->where('codigo_loja', $storeCode)
                ->orWhere('codigo_loja', ltrim($storeCode, '0'))
                ->first();

            if ($store) {
                return $store;
            }
        }

        if ($storeName) {
            return Store::query()
                ->where('nome_loja', 'like', '%' . $storeName . '%')
                ->first();
        }

        return null;
    }

    private function resolveMachine(Store $store, ?string $serialNumber): ?Machine
    {
        if (!$serialNumber) {
            return null;
        }

        return Machine::query()
            ->where('serial_number', $serialNumber)
            ->where('store_id', $store->id)
            ->first();
    }

    private function resolveUser(?string $name): ?User
    {
        $name = $this->clean($name);
        if (!$name) {
            return null;
        }

        $normalized = $this->normalizePerson($name);

        return User::query()
            ->whereNull('deleted_at')
            ->get(['id', 'name', 'email'])
            ->first(function (User $user) use ($normalized) {
                $haystack = $this->normalizePerson($user->name ?: $user->email);
                return $haystack === $normalized || Str::contains($haystack, $normalized) || Str::contains($normalized, $haystack);
            });
    }

    private function mapStatus(?string $value): string
    {
        $normalized = $this->normalizeText($value);

        return match (true) {
            Str::contains($normalized, 'conclu') => 'concluido',
            Str::contains($normalized, 'nao conclu'),
            Str::contains($normalized, 'não conclu') => 'pendente',
            Str::contains($normalized, 'agend') => 'agendado',
            Str::contains($normalized, 'cancel') => 'cancelado',
            Str::contains($normalized, 'peca') || Str::contains($normalized, 'peça') => 'aguarda_peca',
            default => 'pendente',
        };
    }

    private function mapPriority(?string $value): string
    {
        $normalized = $this->normalizeText($value);

        return match (true) {
            Str::contains($normalized, 'alta') => 'alta',
            Str::contains($normalized, 'baixa') => 'baixa',
            default => 'media',
        };
    }

    private function mapServiceType(?string $value): ?string
    {
        $normalized = $this->normalizeText($value);

        return match (true) {
            Str::contains($normalized, 'software') => 'software',
            Str::contains($normalized, 'manuten') => 'manutencao',
            Str::contains($normalized, 'pre') && Str::contains($normalized, 'visita') => 'pre_visita',
            Str::contains($normalized, 'repar'),
            Str::contains($normalized, 'assist') => 'reparacao',
            default => null,
        };
    }

    private function mapZone(?string $value): ?string
    {
        $normalized = $this->normalizeText($value);

        return self::ZONES[$normalized] ?? null;
    }

    private function parseDate(?string $value): ?Carbon
    {
        $value = $this->clean($value);
        if (!$value) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
            } catch (\Throwable $e) {
            }
        }

        foreach (['d/m/y', 'd/m/Y', 'Y-m-d', 'd-m-Y', 'd-m-y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseTime(?string $value): ?Carbon
    {
        $value = $this->clean($value);
        if (!$value) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
            } catch (\Throwable $e) {
            }
        }

        foreach (['H:i', 'H:i:s'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable $e) {
            }
        }

        return null;
    }

    private function extractStoreCode(?string $value): ?string
    {
        $value = $this->clean($value);
        if (!$value) {
            return null;
        }

        if (preg_match('/\bL?\d+\b/i', $value, $matches)) {
            return strtoupper($matches[0]);
        }

        return null;
    }

    private function cleanStoreName(?string $value): ?string
    {
        $value = $this->clean($value);
        if (!$value) {
            return null;
        }

        if (preg_match('/^\s*[A-Za-z]?\d+\s*-\s*(.+)$/', $value, $matches)) {
            return trim($matches[1]);
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $keys
     */
    private function value(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && trim((string) $row[$key]) !== '') {
                return (string) $row[$key];
            }
        }

        return null;
    }

    private function normalizeHeader(string $value): string
    {
        return Str::of($value)
            ->replace(["\r", "\n"], ' ')
            ->ascii()
            ->lower()
            ->replace('/', ' ')
            ->replace('-', ' ')
            ->replace('.', '')
            ->replace(':', ' ')
            ->squish()
            ->replace(' ', '_')
            ->value();
    }

    private function normalizeText(?string $value): string
    {
        return Str::of((string) $value)
            ->ascii()
            ->lower()
            ->squish()
            ->value();
    }

    private function normalizePerson(?string $value): string
    {
        return Str::of((string) $value)
            ->ascii()
            ->lower()
            ->replace(['.', ',', ';', ':', '-', '_'], ' ')
            ->squish()
            ->value();
    }

    private function clean(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    /**
     * @param  array<int, string|null>  $parts
     */
    private function mergeNotes(array $parts): ?string
    {
        $lines = collect($parts)
            ->filter(fn ($value) => $value !== null && trim((string) $value) !== '')
            ->map(fn ($value) => trim((string) $value))
            ->unique()
            ->values()
            ->all();

        return empty($lines) ? null : implode("\n", $lines);
    }
}
