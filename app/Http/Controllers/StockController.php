<?php

namespace App\Http\Controllers;

use App\Exports\StockItemsExport;
use App\Exports\StockMovementsExport;
use App\Exports\TechnicianStocksExport;
use App\Http\Requests\StockOperationRequest;
use App\Http\Requests\UpsertItemRequest;
use App\Models\Item;
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

    public function technicians(Request $request)
    {
        $technicians = $this->getTechnicians()
            ->when(!$this->canManageWarehouse(), fn ($collection) => $collection->where('id', auth()->id()))
            ->values();

        $stocks = TechnicianItemStock::query()
            ->with(['item', 'technician'])
            ->where('quantity', '>', 0)
            ->whereIn('technician_id', $technicians->pluck('id'))
            ->get()
            ->groupBy('technician_id');

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

        DB::transaction(function () use ($validated, $technician) {
            $item = Item::query()->lockForUpdate()->findOrFail($validated['item_id']);
            $technicianStock = $this->getTechnicianStockForUpdate($technician->id, $item->id);

            if ($technicianStock->quantity < $validated['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => __('O técnico não tem stock suficiente para consumir essa quantidade.'),
                ]);
            }

            $technicianStock->decrement('quantity', $validated['quantity']);
            $this->cleanupEmptyTechnicianStock($technicianStock);

            $this->recordMovement($item, 'consumed', $validated['quantity'], $technician->id, $technician->name ?: $technician->email, 'Consumo em assistência', $validated['notes'] ?? null);
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
        ?string $notes
    ): void {
        StockMovement::create([
            'item_id' => $item->id,
            'part_id' => $item->id,
            'technician_id' => $technicianId,
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
