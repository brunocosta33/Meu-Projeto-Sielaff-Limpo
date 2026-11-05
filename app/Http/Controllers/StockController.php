<?php

namespace App\Http\Controllers;

use App\Exports\StockItemsExport;
use App\Exports\StockMovementsExport;
use App\Exports\TechnicianStocksExport;
use App\Http\Requests\StockOperationRequest;
use App\Http\Requests\UpsertItemRequest;
use App\Models\Item;
use Illuminate\Validation\Rule;
use App\Models\Machine;
use App\Models\StockMovement;
use App\Models\TechnicianItemStock;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query()
            ->withSum('technicianStocks as technician_stock_total', 'quantity')
            ->orderBy('reference');

        if ($request->filled('q')) {
            $term = trim((string) $request->q);

            $query->where(function ($builder) use ($term) {
                $builder->where('reference', 'like', '%' . $term . '%')
                    ->orWhere('name', 'like', '%' . $term . '%')
                    ->orWhere('description', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('warehouse_stock', '<=', 'minimum_stock');
        }

        $items = $query->get();

        return view('backoffice.stock.items.index', [
            'items' => $items,
            'activeItems' => Item::query()->where('is_active', true)->orderBy('reference')->get(),
            'technicians' => $this->getTechnicians(),
            'machines' => $this->getMachines(),
            'movementTypes' => StockMovement::TYPES,
            'summary' => [
                'items' => $items->count(),
                'warehouse_stock' => $items->sum('warehouse_stock'),
                'technician_stock' => $items->sum(fn ($item) => (int) ($item->technician_stock_total ?? 0)),
                'total_stock' => $items->sum(fn ($item) => (int) $item->warehouse_stock + (int) ($item->technician_stock_total ?? 0)),
                'low_stock' => $items->filter(fn ($item) => $item->warehouse_stock <= $item->minimum_stock)->count(),
            ],
            'canManageWarehouse' => $this->canManageWarehouse(),
        ]);
    }

    public function create()
    {
        abort_unless($this->canManageWarehouse(), 403);

        return view('backoffice.stock.items.create', [
            'item' => new Item([
                'warehouse_stock' => 0,
                'minimum_stock' => 0,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(UpsertItemRequest $request)
    {
        abort_unless($this->canManageWarehouse(), 403);

        Item::create($this->normalizeItemData($request));

        flash(__('Peça criada com sucesso.'))->success();

        return redirect()->route('backoffice.stock.items.index');
    }

    public function edit(Item $item)
    {
        abort_unless($this->canManageWarehouse(), 403);

        return view('backoffice.stock.items.edit', compact('item'));
    }

    public function update(UpsertItemRequest $request, Item $item)
    {
        abort_unless($this->canManageWarehouse(), 403);

        $item->update($this->normalizeItemData($request));

        flash(__('Peça atualizada com sucesso.'))->success();

        return redirect()->route('backoffice.stock.items.index');
    }

    public function movements(Request $request)
    {
        $filters = $request->only(['item_id', 'technician_id', 'movement_type', 'date_from', 'date_to', 'period', 'q']);

        $query = StockMovement::query()
            ->with(['item', 'technician', 'creator'])
            ->applyFilters($filters)
            ->latest();

        if (!$this->canManageWarehouse()) {
            $query->where('technician_id', auth()->id());
        }

        $movements = $query->paginate(50)->withQueryString();

        return view('backoffice.stock.movements.index', [
            'movements' => $movements,
            'items' => Item::query()->orderBy('reference')->get(),
            'technicians' => $this->getTechnicians(),
            'movementTypes' => StockMovement::TYPES,
            'canManageWarehouse' => $this->canManageWarehouse(),
        ]);
    }

    /**
     * Entregas e devoluções: o registo simples de o que saiu para cada técnico.
     * Não mexe em saldos — o que está com cada um é somado do próprio registo.
     */
    public function deliveries(Request $request)
    {
        abort_unless($this->canManageWarehouse(), 403);

        $movements = StockMovement::query()
            ->whereIn('movement_type', ['to_technician', 'from_technician'])
            ->when($request->filled('technician_id'), fn ($q) => $q->where('technician_id', $request->technician_id))
            ->when($request->filled('item_id'), fn ($q) => $q->where('item_id', $request->item_id))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('moved_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('moved_at', '<=', $request->date_to))
            ->when($request->filled('q'), function ($q) use ($request) {
                $termo = '%' . trim($request->q) . '%';
                $q->where(fn ($w) => $w->where('notes', 'like', $termo)
                    ->orWhereHas('item', fn ($i) => $i->where('name', 'like', $termo)->orWhere('reference', 'like', $termo))
                    ->orWhereHas('technician', fn ($t) => $t->where('name', 'like', $termo)));
            })
            ->with(['item', 'technician', 'creator'])
            ->orderByDesc('moved_at')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('backoffice.stock.deliveries.index', [
            'movements' => $movements,
            'items' => Item::where('is_active', true)->orderBy('name')->get(['id', 'reference', 'name']),
            'technicians' => $this->getTechnicians(),
            'holdings' => $this->technicianHoldings(),
        ]);
    }

    /** Registar uma entrega ou uma devolução. O artigo pode ser criado aqui, se ainda não existir. */
    public function storeDelivery(Request $request)
    {
        abort_unless($this->canManageWarehouse(), 403);

        $data = $request->validate([
            'direcao' => ['required', Rule::in(['entrega', 'devolucao'])],
            'technician_id' => ['required', 'exists:users,id'],
            'item_id' => ['nullable', 'required_without:novo_nome', 'exists:items,id'],
            'novo_nome' => ['nullable', 'required_without:item_id', 'string', 'max:255'],
            'novo_referencia' => ['nullable', 'string', 'max:255', 'unique:items,reference'],
            'quantity' => ['required', 'integer', 'min:1'],
            'moved_at' => ['required', 'date', 'before_or_equal:now'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ], [
            'technician_id.required' => __('Escolha a quem está a entregar.'),
            'item_id.required_without' => __('Escolha o artigo ou crie um novo.'),
            'novo_nome.required_without' => __('Dê um nome ao artigo novo.'),
            'novo_referencia.unique' => __('Já existe um artigo com essa referência.'),
            'quantity.min' => __('A quantidade tem de ser pelo menos 1.'),
            'moved_at.before_or_equal' => __('A data e hora não podem ser no futuro.'),
        ]);

        $item = empty($data['item_id'])
            ? Item::create([
                // validated() não traz as chaves opcionais que não vieram no formulário
                'reference' => ($data['novo_referencia'] ?? '') ?: $this->nextReference(),
                'name' => $data['novo_nome'],
                'is_active' => true,
            ])
            : Item::findOrFail($data['item_id']);

        $technician = $this->findTechnician($data['technician_id']);
        $entrega = $data['direcao'] === 'entrega';
        $quemE = $technician->name ?: $technician->email;

        StockMovement::create([
            'item_id' => $item->id,
            'technician_id' => $technician->id,
            'movement_type' => $entrega ? 'to_technician' : 'from_technician',
            'quantity' => $data['quantity'],
            'source' => $entrega ? __('Armazém') : $quemE,
            'destination' => $entrega ? $quemE : __('Armazém'),
            'notes' => $data['notes'] ?? null,
            'moved_at' => $data['moved_at'],
            'created_by' => auth()->id(),
        ]);

        flash($entrega
            ? __('Entrega registada: :qtd x :artigo para :quem.', ['qtd' => $data['quantity'], 'artigo' => $item->name, 'quem' => $quemE])
            : __('Devolução registada: :qtd x :artigo de :quem.', ['qtd' => $data['quantity'], 'artigo' => $item->name, 'quem' => $quemE]))->success();

        return back();
    }

    /**
     * O que está com cada técnico, somado do registo: entregue menos devolvido e consumido.
     * Não há saldo guardado — o registo é a única verdade.
     */
    private function technicianHoldings()
    {
        return StockMovement::query()
            ->whereNotNull('technician_id')
            ->whereNotNull('item_id')
            ->selectRaw("technician_id, item_id, SUM(CASE WHEN movement_type = 'to_technician' THEN quantity"
                . " WHEN movement_type IN ('from_technician', 'consumed') THEN -quantity ELSE 0 END) as quantity")
            ->groupBy('technician_id', 'item_id')
            ->havingRaw('quantity <> 0')
            ->with('item')
            ->get()
            ->groupBy('technician_id');
    }

    /** Referência para um artigo criado na hora sem referência própria. */
    private function nextReference(): string
    {
        do {
            $ref = 'S/REF-' . str_pad((string) (Item::count() + random_int(1, 99)), 4, '0', STR_PAD_LEFT);
        } while (Item::where('reference', $ref)->exists());

        return $ref;
    }

    public function technicians(Request $request)
    {
        $technicians = $this->getTechnicians()
            ->when(!$this->canManageWarehouse(), fn ($collection) => $collection->where('id', auth()->id()))
            ->values();

        // Calculado do registo de movimentos, para refletir também as entregas feitas no ecrã simples
        $stocks = $this->technicianHoldings();

        return view('backoffice.stock.technicians.index', [
            'technicians' => $technicians,
            'stocks' => $stocks,
            'canManageWarehouse' => $this->canManageWarehouse(),
        ]);
    }

    public function exportItems(Request $request)
    {
        abort_unless($this->canManageWarehouse(), 403);

        return Excel::download(
            new StockItemsExport($request->only(['q', 'is_active', 'low_stock'])),
            'stock_armazem_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportMovements(Request $request)
    {
        return Excel::download(
            new StockMovementsExport(
                $request->only(['item_id', 'technician_id', 'movement_type', 'date_from', 'date_to', 'period', 'q']),
                $this->canManageWarehouse()
            ),
            'movimentos_stock_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportTechnicians()
    {
        $technicians = $this->getTechnicians()
            ->when(!$this->canManageWarehouse(), fn ($collection) => $collection->where('id', auth()->id()))
            ->values();

        return Excel::download(
            new TechnicianStocksExport($technicians, $this->canManageWarehouse()),
            'stock_tecnicos_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportTechnician(User $technician)
    {
        abort_unless($this->canManageWarehouse() || $technician->id === auth()->id(), 403);

        $slug = Str::slug($technician->name ?: $technician->email) ?: $technician->id;

        return Excel::download(
            new TechnicianStocksExport(collect([$technician]), $this->canManageWarehouse()),
            'stock_tecnico_' . $slug . '_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function warehouseIn(StockOperationRequest $request)
    {
        abort_unless($this->canManageWarehouse(), 403);

        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $item = Item::query()->lockForUpdate()->findOrFail($validated['item_id']);
            $item->increment('warehouse_stock', $validated['quantity']);

            $this->recordMovement($item, 'warehouse_in', $validated['quantity'], null, 'Entrada externa', 'Armazém', $validated['notes'] ?? null);
        });

        flash(__('Stock adicionado ao armazém com sucesso.'))->success();

        return $this->stockResponse(__('Stock adicionado ao armazém com sucesso.'), $validated['item_id']);
    }

    public function transfer(StockOperationRequest $request)
    {
        abort_unless($this->canManageWarehouse(), 403);

        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $item = Item::query()->lockForUpdate()->findOrFail($validated['item_id']);

            if ($item->warehouse_stock < $validated['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => __('Não existe stock suficiente em armazém para esta transferência.'),
                ]);
            }

            $technician = $this->findTechnician($validated['technician_id']);
            $technicianStock = $this->getTechnicianStockForUpdate($technician->id, $item->id);

            $item->decrement('warehouse_stock', $validated['quantity']);
            $technicianStock->increment('quantity', $validated['quantity']);

            $this->recordMovement($item, 'to_technician', $validated['quantity'], $technician->id, 'Armazém', $technician->name ?: $technician->email, $validated['notes'] ?? null);
        });

        flash(__('Peça transferida para o técnico com sucesso.'))->success();

        return $this->stockResponse(__('Peça transferida para o técnico com sucesso.'), $validated['item_id']);
    }

    public function returnFromTechnician(StockOperationRequest $request)
    {
        $validated = $request->validated();
        $technician = $this->resolveTechnicianForOperation($validated['technician_id'] ?? null);

        DB::transaction(function () use ($validated, $technician) {
            $item = Item::query()->lockForUpdate()->findOrFail($validated['item_id']);
            $technicianStock = $this->getTechnicianStockForUpdate($technician->id, $item->id);

            if ($technicianStock->quantity < $validated['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => __('O técnico não tem stock suficiente para devolver essa quantidade.'),
                ]);
            }

            $technicianStock->decrement('quantity', $validated['quantity']);
            $item->increment('warehouse_stock', $validated['quantity']);
            $this->cleanupEmptyTechnicianStock($technicianStock);

            $this->recordMovement($item, 'from_technician', $validated['quantity'], $technician->id, $technician->name ?: $technician->email, 'Armazém', $validated['notes'] ?? null);
        });

        flash(__('Devolução registada com sucesso.'))->success();

        return $this->stockResponse(__('Devolução registada com sucesso.'), $validated['item_id']);
    }

    public function consume(StockOperationRequest $request)
    {
        $validated = $request->validated();
        $technician = $this->resolveTechnicianForOperation($validated['technician_id'] ?? null);
        $machine = Machine::with('store')->findOrFail($validated['machine_id']);
        $destination = $this->machineDestinationLabel($machine);

        DB::transaction(function () use ($validated, $technician, $machine, $destination) {
            $item = Item::query()->lockForUpdate()->findOrFail($validated['item_id']);
            $technicianStock = $this->getTechnicianStockForUpdate($technician->id, $item->id);

            if ($technicianStock->quantity < $validated['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => __('O técnico não tem stock suficiente para consumir essa quantidade.'),
                ]);
            }

            $technicianStock->decrement('quantity', $validated['quantity']);
            $this->cleanupEmptyTechnicianStock($technicianStock);

            $this->recordMovement(
                $item,
                'consumed',
                $validated['quantity'],
                $technician->id,
                $technician->name ?: $technician->email,
                $destination,
                $validated['notes'] ?? null,
                $machine->id
            );
        });

        flash(__('Consumo de peça registado com sucesso.'))->success();

        return $this->stockResponse(__('Consumo de peça registado com sucesso.'), $validated['item_id']);
    }

    public function adjust(StockOperationRequest $request)
    {
        abort_unless($this->canManageWarehouse(), 403);

        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $item = Item::query()->lockForUpdate()->findOrFail($validated['item_id']);
            $quantity = (int) $validated['quantity'];
            $scope = $validated['adjustment_scope'];

            if ($scope === 'warehouse') {
                $newQuantity = $item->warehouse_stock + $quantity;

                if ($newQuantity < 0) {
                    throw ValidationException::withMessages([
                    'quantity' => __('O ajuste deixa o armazém com stock negativo.'),
                ]);
                }

                $item->update(['warehouse_stock' => $newQuantity]);

                $this->recordMovement($item, 'adjustment', $quantity, null, 'Ajuste armazém', 'Armazém', $validated['notes'] ?? null);

                return;
            }

            $technician = $this->findTechnician($validated['technician_id']);
            $technicianStock = $this->getTechnicianStockForUpdate($technician->id, $item->id);
            $newQuantity = $technicianStock->quantity + $quantity;

            if ($newQuantity < 0) {
                throw ValidationException::withMessages([
                    'quantity' => __('O ajuste deixa o técnico com stock negativo.'),
                ]);
            }

            $technicianStock->update(['quantity' => $newQuantity]);
            $this->cleanupEmptyTechnicianStock($technicianStock);

            $this->recordMovement($item, 'adjustment', $quantity, $technician->id, 'Ajuste técnico', $technician->name ?: $technician->email, $validated['notes'] ?? null);
        });

        flash(__('Ajuste manual registado com sucesso.'))->success();

        return $this->stockResponse(__('Ajuste manual registado com sucesso.'), $validated['item_id']);
    }

    public function destroyMovement(StockMovement $movement)
    {
        abort_unless($this->canManageWarehouse(), 403);

        try {
            DB::transaction(function () use ($movement) {
                $item = Item::query()->lockForUpdate()->findOrFail($movement->item_id);
                $quantity = (int) $movement->quantity;
                $type = $movement->movement_type;

                // Efeito a aplicar ao armazém para reverter o movimento.
                $warehouseDelta = match ($type) {
                    'warehouse_in' => -$quantity,
                    'to_technician' => $quantity,
                    'from_technician' => -$quantity,
                    'adjustment' => $movement->technician_id ? 0 : -$quantity,
                    default => 0, // consumed
                };

                // Efeito a aplicar ao stock do técnico (apenas se o movimento envolveu um técnico).
                $technicianDelta = 0;
                if ($movement->technician_id) {
                    $technicianDelta = match ($type) {
                        'to_technician' => -$quantity,
                        'from_technician' => $quantity,
                        'consumed' => $quantity,
                        'adjustment' => -$quantity,
                        default => 0,
                    };
                }

                if ($warehouseDelta !== 0) {
                    $newWarehouse = (int) $item->warehouse_stock + $warehouseDelta;

                    if ($newWarehouse < 0) {
                        throw new \RuntimeException(__('Não é possível remover este movimento: deixaria o armazém com stock negativo. Verifique se as peças já foram movimentadas entretanto.'));
                    }

                    $item->update(['warehouse_stock' => $newWarehouse]);
                }

                if ($technicianDelta !== 0) {
                    $technicianStock = $this->getTechnicianStockForUpdate($movement->technician_id, $item->id);
                    $newQuantity = (int) $technicianStock->quantity + $technicianDelta;

                    if ($newQuantity < 0) {
                        throw new \RuntimeException(__('Não é possível remover este movimento: deixaria o técnico com stock negativo. Verifique se as peças já foram movimentadas entretanto.'));
                    }

                    $technicianStock->update(['quantity' => $newQuantity]);
                    $this->cleanupEmptyTechnicianStock($technicianStock);
                }

                $movement->delete();
            });
        } catch (\RuntimeException $e) {
            flash($e->getMessage())->error();

            return redirect()->route('backoffice.stock.movements.index');
        }

        flash(__('Movimento removido e stock reposto com sucesso.'))->success();

        return redirect()->route('backoffice.stock.movements.index');
    }

    private function normalizeItemData(UpsertItemRequest $request): array
    {
        return [
            'reference' => trim((string) $request->reference),
            'name' => trim((string) $request->name),
            'description' => filled($request->description) ? trim((string) $request->description) : null,
            'warehouse_stock' => (int) ($request->warehouse_stock ?? 0),
            'minimum_stock' => (int) $request->minimum_stock,
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    private function recordMovement(
        Item $item,
        string $movementType,
        int $quantity,
        ?int $technicianId,
        ?string $source,
        ?string $destination,
        ?string $notes,
        ?int $machineId = null
    ): void {
        StockMovement::create([
            'item_id' => $item->id,
            'part_id' => $item->id,
            'technician_id' => $technicianId,
            'machine_id' => $machineId,
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'source' => $source,
            'destination' => $destination,
            'notes' => $notes,
            'moved_at' => Carbon::now(),
            'created_by' => auth()->id(),
        ]);
    }

    private function getTechnicianStockForUpdate(int $technicianId, int $itemId): TechnicianItemStock
    {
        $stock = TechnicianItemStock::query()
            ->where('technician_id', $technicianId)
            ->where('item_id', $itemId)
            ->lockForUpdate()
            ->first();

        if ($stock) {
            return $stock;
        }

        TechnicianItemStock::create([
            'technician_id' => $technicianId,
            'item_id' => $itemId,
            'quantity' => 0,
        ]);

        return TechnicianItemStock::query()
            ->where('technician_id', $technicianId)
            ->where('item_id', $itemId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function cleanupEmptyTechnicianStock(TechnicianItemStock $stock): void
    {
        $freshStock = $stock->fresh();

        if ($freshStock && $freshStock->quantity === 0) {
            $freshStock->delete();
        }
    }

    private function getTechnicians()
    {
        return User::query()
            ->whereHas('role', function ($query) {
                $query->where('name', 'user');
            })
            ->orderBy('name')
            ->get();
    }

    private function machineDestinationLabel(Machine $machine): string
    {
        $parts = [];

        if ($machine->store) {
            $parts[] = trim(($machine->store->codigo_loja ? $machine->store->codigo_loja . ' - ' : '') . $machine->store->nome_loja);
        }

        $parts[] = __('Nº Série') . ': ' . $machine->serial_number;

        return implode(' · ', array_filter($parts));
    }

    private function getMachines()
    {
        return Machine::query()
            ->with('store')
            ->whereHas('store')
            ->get()
            ->sortBy(fn (Machine $machine) => $machine->store->codigo_loja . ' ' . $machine->serial_number)
            ->values();
    }

    private function findTechnician(int $technicianId): User
    {
        return $this->getTechnicians()->firstWhere('id', $technicianId)
            ?? User::query()->findOrFail($technicianId);
    }

    private function resolveTechnicianForOperation(?int $technicianId): User
    {
        if ($this->canManageWarehouse()) {
            return $this->findTechnician((int) $technicianId);
        }

        return auth()->user();
    }

    private function canManageWarehouse(): bool
    {
        return !auth()->user()->hasRole('user');
    }

    private function stockResponse(string $message, int $itemId)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'message' => $message,
                'item' => $this->buildItemPayload($itemId),
                'summary' => $this->buildSummaryPayload(),
            ]);
        }

        return redirect()->route('backoffice.stock.items.index');
    }

    private function buildItemPayload(int $itemId): array
    {
        $item = Item::query()
            ->withSum('technicianStocks as technician_stock_total', 'quantity')
            ->findOrFail($itemId);

        return [
            'id' => $item->id,
            'reference' => $item->reference,
            'name' => $item->name,
            'warehouse_stock' => $item->warehouse_stock,
            'minimum_stock' => $item->minimum_stock,
            'technician_stock_total' => (int) ($item->technician_stock_total ?? 0),
            'total_stock' => (int) $item->warehouse_stock + (int) ($item->technician_stock_total ?? 0),
            'is_active' => (bool) $item->is_active,
            'is_low_stock' => $item->warehouse_stock <= $item->minimum_stock,
        ];
    }

    private function buildSummaryPayload(): array
    {
        $items = Item::query()
            ->withSum('technicianStocks as technician_stock_total', 'quantity')
            ->get();

        return [
            'items' => $items->count(),
            'warehouse_stock' => $items->sum('warehouse_stock'),
            'technician_stock' => $items->sum(fn ($item) => (int) ($item->technician_stock_total ?? 0)),
            'total_stock' => $items->sum(fn ($item) => (int) $item->warehouse_stock + (int) ($item->technician_stock_total ?? 0)),
            'low_stock' => $items->filter(fn ($item) => $item->warehouse_stock <= $item->minimum_stock)->count(),
        ];
    }
}
