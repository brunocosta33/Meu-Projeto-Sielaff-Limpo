<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Machine;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;


class StoreController extends Controller
{
    private const INSIGNIAS = [
        'lidl' => 'Lidl',
        'sonae' => 'Sonae',
    ];

    public function index()
    {
        $query = Store::with('machines')->orderBy('codigo_loja');

        if (request('q')) {
            $term = trim((string) request('q'));
            $query->where(function ($q) use ($term) {
                $q->where('codigo_loja', 'like', '%' . $term . '%')
                    ->orWhere('nome_loja', 'like', '%' . $term . '%')
                    ->orWhere('morada', 'like', '%' . $term . '%')
                    ->orWhere('cidade', 'like', '%' . $term . '%')
                    ->orWhere('regiao', 'like', '%' . $term . '%');
            });
        }

        if (request('insignia')) {
            $query->where('insignia', request('insignia'));
        }

        $stores = $query->get();
        return view('backoffice.stores.index', [
            'stores' => $stores,
            'insignias' => self::INSIGNIAS,
            'canManageStores' => $this->canManageStores(),
        ]);
    }


    public function create()
    {
        abort_unless($this->canManageStores(), 403);

        return view('backoffice.stores.create', [
            'insignias' => self::INSIGNIAS,
            'machineRows' => collect(old('machines', [['serial_number' => '', 'ip_address' => '', 'descricao' => '']])),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($this->canManageStores(), 403);

        $data = $this->validateStore($request);

        DB::transaction(function () use ($data) {
            $store = Store::create($data['store']);
            $this->syncMachines($store, $data['machines']);
        });

        flash('Loja criada com sucesso!')->success();
        return redirect()->route('backoffice.stores.index');
    }

    public function edit($id)
    {
        abort_unless($this->canManageStores(), 403);

        $store = Store::findOrFail($id);
        return view('backoffice.stores.edit', [
            'store' => $store,
            'insignias' => self::INSIGNIAS,
            'machineRows' => old('machines', $store->machines->map(function ($machine) {
                return [
                    'id' => $machine->id,
                    'serial_number' => $machine->serial_number,
                    'ip_address' => $machine->ip_address,
                    'descricao' => $machine->descricao,
                ];
            })->values()->all()),
        ]);
    }

    public function update(Request $request, $id)
    {
        abort_unless($this->canManageStores(), 403);

        $store = Store::findOrFail($id);
        $data = $this->validateStore($request, $store);

        DB::transaction(function () use ($store, $data) {
            $store->update($data['store']);
            $this->syncMachines($store, $data['machines']);
        });

        flash('Loja atualizada com sucesso!')->success();
        return redirect()->route('backoffice.stores.index');
    }

    public function delete($id)
    {
        abort_unless($this->canManageStores(), 403);

        Store::where('id', $id)->update(['deleted_at' => now()]);
        flash('Loja apagada com sucesso!')->success();
        return redirect()->route('backoffice.stores.index');
    }

    private function canManageStores(): bool
    {
        $user = auth()->user();

        return $user && !$user->hasRole('user');
    }

    private function validateStore(Request $request, ?Store $store = null): array
    {
        $validated = $request->validate([
            'regiao' => 'required|string|max:255',
            'insignia' => 'required|in:' . implode(',', array_keys(self::INSIGNIAS)),
            'codigo_loja' => 'required|string|max:255',
            'nome_loja' => 'required|string|max:255',
            'morada' => 'nullable|string',
            'cidade' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:50',
            'contacto_loja' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'observacoes' => 'nullable|string',
            'machines' => 'nullable|array',
            'machines.*.id' => [
                'nullable',
                'integer',
                Rule::exists('machines', 'id')->where(function ($query) use ($store) {
                    if ($store) {
                        $query->where('store_id', $store->id);
                    }
                }),
            ],
            'machines.*.serial_number' => 'nullable|string|max:255',
            'machines.*.ip_address' => 'nullable|ip',
            'machines.*.descricao' => 'nullable|string',
        ]);

        $machines = collect($validated['machines'] ?? [])
            ->map(function ($machine) {
                return [
                    'id' => $machine['id'] ?? null,
                    'serial_number' => trim((string) ($machine['serial_number'] ?? '')),
                    'ip_address' => trim((string) ($machine['ip_address'] ?? '')) ?: null,
                    'descricao' => trim((string) ($machine['descricao'] ?? '')) ?: null,
                ];
            })
            ->filter(function ($machine) {
                return $machine['serial_number'] !== '' || $machine['ip_address'] || $machine['descricao'];
            })
            ->values();

        $keptMachineIds = $machines->pluck('id')->filter()->map(function ($id) {
            return (int) $id;
        })->all();

        $this->validateSubmittedMachineDuplicates($machines);

        $machines->each(function ($machine, $index) use ($store, $keptMachineIds) {
            $machineId = $machine['id'];

            validator($machine, [
                'serial_number' => [
                    'required',
                    'max:255',
                    $this->uniqueActiveStoreMachine('serial_number', $machineId, $store, $keptMachineIds),
                ],
                'ip_address' => [
                    'nullable',
                    'ip',
                    $this->uniqueActiveStoreMachine('ip_address', $machineId, $store, $keptMachineIds),
                ],
                'descricao' => 'nullable|string',
            ], [], [
                'serial_number' => 'máquina ' . ($index + 1) . ' número de série',
                'ip_address' => 'máquina ' . ($index + 1) . ' IP',
            ])->validate();
        });

        return [
            'store' => collect($validated)->except('machines')->all(),
            'machines' => $machines->all(),
        ];
    }

    private function uniqueActiveStoreMachine(string $column, ?int $ignoreId = null, ?Store $store = null, array $keptMachineIds = [])
    {
        return Rule::unique('machines', $column)
            ->ignore($ignoreId)
            ->where(function ($query) use ($store, $keptMachineIds) {
                $query->whereIn('store_id', function ($storeQuery) {
                    $storeQuery->select('id')
                        ->from('stores')
                        ->whereNull('deleted_at');
                });

                if ($store) {
                    $query->where(function ($activeMachinesQuery) use ($store, $keptMachineIds) {
                        $activeMachinesQuery->where('store_id', '<>', $store->id);

                        if (!empty($keptMachineIds)) {
                            $activeMachinesQuery->orWhereIn('id', $keptMachineIds);
                        }
                    });
                }
            });
    }

    private function validateSubmittedMachineDuplicates($machines): void
    {
        $seenSerialNumbers = [];
        $seenIpAddresses = [];
        $errors = [];

        foreach ($machines as $index => $machine) {
            $serialNumber = mb_strtolower($machine['serial_number']);
            $ipAddress = $machine['ip_address'];

            if ($serialNumber !== '') {
                if (isset($seenSerialNumbers[$serialNumber])) {
                    $errors['machines.' . $index . '.serial_number'] = 'O número de série da máquina ' . ($index + 1) . ' está repetido no formulário.';
                }

                $seenSerialNumbers[$serialNumber] = true;
            }

            if ($ipAddress) {
                if (isset($seenIpAddresses[$ipAddress])) {
                    $errors['machines.' . $index . '.ip_address'] = 'O IP da máquina ' . ($index + 1) . ' está repetido no formulário.';
                }

                $seenIpAddresses[$ipAddress] = true;
            }
        }

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    private function syncMachines(Store $store, array $machines): void
    {
        $existingIds = $store->machines()->pluck('id')->all();
        $submittedIds = collect($machines)->pluck('id')->filter()->map(function ($id) {
            return (int) $id;
        })->all();

        $idsToDelete = array_diff($existingIds, $submittedIds);
        if (!empty($idsToDelete)) {
            Machine::whereIn('id', $idsToDelete)->delete();
        }

        $keptIds = [];

        foreach ($machines as $machineData) {
            $machine = null;

            if (!empty($machineData['id'])) {
                $machine = $store->machines()->where('id', $machineData['id'])->first();
            }

            $payload = [
                'serial_number' => $machineData['serial_number'],
                'ip_address' => $machineData['ip_address'],
                'descricao' => $machineData['descricao'],
            ];

            if ($machine) {
                $machine->update($payload);
                $keptIds[] = $machine->id;
                continue;
            }

            $newMachine = $store->machines()->create($payload);
            $keptIds[] = $newMachine->id;
        }
    }
}
