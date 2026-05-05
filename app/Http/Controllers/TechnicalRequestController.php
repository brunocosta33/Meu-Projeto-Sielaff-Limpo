<?php

namespace App\Http\Controllers;

use App\Models\TechnicalRequest;
use App\Models\TechnicalRequestFile;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TechnicalRequestsExport;

class TechnicalRequestController extends Controller
{
    private const ZONES = [
        'norte' => 'Norte',
        'centro' => 'Centro',
        'sul' => 'Sul',
    ];

    private const SERVICE_TYPES = [
        'software' => 'Software',
        'reparacao' => 'Assistência/Reparação',
        'manutencao' => 'Manutenção',
        'pre_visita' => 'Pré-Visita',
    ];

    private const PRIORITIES = [
        'baixa' => 'Baixa',
        'media' => 'Média',
        'alta' => 'Alta',
    ];

    private const STATUSES = [
        'pendente' => 'Pendente',
        'agendado' => 'Agendado',
        'concluido' => 'Concluído',
        'cancelado' => 'Cancelado',
        'aguarda_peca' => 'Aguarda Peça',
    ];

    public function index(Request $request)
    {
        $isAdmin = $this->isAdmin();
        $technicians = $this->getAssignableTechnicians();

        $query = TechnicalRequest::with(['store', 'machine', 'creator', 'editor', 'assignedTechnician', 'files'])
            ->orderByRaw("FIELD(estado, 'pendente', 'agendado', 'aguarda_peca', 'concluido', 'cancelado')")
            ->orderBy('data_pedido', 'desc');

        if (!$isAdmin) {
            $query->where('assigned_technician_id', auth()->id());
        }

        if ($request->filled('q')) {
            $term = trim($request->q);

            $query->where(function ($q) use ($term) {
                $q->where('origem', 'like', '%' . $term . '%')
                    ->orWhere('descricao_problema', 'like', '%' . $term . '%')
                    ->orWhere('observacoes', 'like', '%' . $term . '%')
                    ->orWhereHas('assignedTechnician', function ($technicianQuery) use ($term) {
                        $technicianQuery->where('name', 'like', '%' . $term . '%')
                            ->orWhere('email', 'like', '%' . $term . '%');
                    })
                    ->orWhereHas('store', function ($storeQuery) use ($term) {
                        $storeQuery->where('codigo_loja', 'like', '%' . $term . '%')
                            ->orWhere('nome_loja', 'like', '%' . $term . '%')
                            ->orWhere('insignia', 'like', '%' . $term . '%')
                            ->orWhere('cidade', 'like', '%' . $term . '%');
                    })
                    ->orWhereHas('machine', function ($machineQuery) use ($term) {
                        $machineQuery->where('serial_number', 'like', '%' . $term . '%');
                    });
            });
        }

        if ($request->filled('codigo_loja')) {
            $query->whereHas('store', function ($q) use ($request) {
                $q->where('codigo_loja', 'like', '%' . $request->codigo_loja . '%');
            });
        }

        if ($request->filled('prioridade')) {
            $query->where('prioridade', $request->prioridade);
        }

        if ($request->filled('zona')) {
            $query->where('zona', $request->zona);
        }

        if ($request->filled('tipo_servico')) {
            $query->where('tipo_servico', $request->tipo_servico);
        }

        if ($isAdmin && $request->filled('assigned_technician_id')) {
            if ($request->assigned_technician_id === 'unassigned') {
                $query->whereNull('assigned_technician_id');
            } else {
                $query->where('assigned_technician_id', $request->assigned_technician_id);
            }
        }

        if ($request->filled('serial_number')) {
            $serialNumber = trim($request->serial_number);
            $query->whereHas('machine', function ($machineQuery) use ($serialNumber) {
                $machineQuery->where('serial_number', 'like', '%' . $serialNumber . '%');
            });
        }

        $this->applyDateFilters($query, $request);

        $statsRequests = (clone $query)->get();
        $stats = [
            'total' => $statsRequests->count(),
            'pendente' => $statsRequests->where('estado', 'pendente')->count(),
            'agendado' => $statsRequests->where('estado', 'agendado')->count(),
            'aguarda_peca' => $statsRequests->where('estado', 'aguarda_peca')->count(),
            'concluido' => $statsRequests->where('estado', 'concluido')->count(),
        ];

        if ($request->filled('estado')) {
            $query->whereIn('estado', (array) $request->estado);
        }

        $requests = $query->get();

        return view('backoffice.technical_requests.index', [
            'requests' => $requests,
            'stats' => $stats,
            'serviceTypes' => self::SERVICE_TYPES,
            'priorities' => self::PRIORITIES,
            'statuses' => self::STATUSES,
            'zones' => self::ZONES,
            'technicians' => $technicians,
            'canManageAll' => $isAdmin,
        ]);
    }


    public function create()
    {
        abort_unless($this->isAdmin(), 403);

        $stores = Store::with(['machines' => function ($query) {
            $query->orderBy('serial_number');
        }])->orderBy('codigo_loja')->get();

        return view('backoffice.technical_requests.create', [
            'stores' => $stores,
            'serviceTypes' => self::SERVICE_TYPES,
            'priorities' => self::PRIORITIES,
            'statuses' => self::STATUSES,
            'zones' => self::ZONES,
            'technicians' => $this->getAssignableTechnicians(),
            'canManageAll' => true,
            'openRequestsByStore' => $this->openRequestsByStore(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($this->isAdmin(), 403);
        $this->validateFiles($request);

        $data = $this->validateTechnicalRequest($request);

        $data = $this->normalizeTechnicalRequestData($data);
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $technicalRequest = TechnicalRequest::create($data);
        $this->storeFiles($request, $technicalRequest);

        return redirect()->route('backoffice.technical_requests.index')
            ->with('success', 'Pedido criado com sucesso!');
    }

    public function show($id)
    {
        $request = TechnicalRequest::with([
            'machine',
            'creator',
            'editor',
            'assignedTechnician',
            'files',
            'store.machines',          // máquinas disponíveis da loja
            'machines.machine',        // máquinas associadas ao pedido
            'machines.parts.item',     // peças aplicadas
            'machines.parts.user'      // técnico que aplicou
        ])->findOrFail($id);

        $this->authorizeRequestAccess($request);

        return view('backoffice.technical_requests.show', [
            'request' => $request,
            'canManageAll' => $this->isAdmin(),
        ]);
    }

    public function edit($id)
    {
        $technicalRequest = TechnicalRequest::with(['machine', 'creator', 'editor', 'assignedTechnician', 'files'])->findOrFail($id);
        $this->authorizeRequestAccess($technicalRequest);
        abort_unless($this->canEditTechnicalRequest($technicalRequest), 403);

        $stores = Store::with(['machines' => function ($query) {
            $query->orderBy('serial_number');
        }])->orderBy('codigo_loja')->get();

        return view('backoffice.technical_requests.edit', [
            'technicalRequest' => $technicalRequest,
            'stores' => $stores,
            'serviceTypes' => self::SERVICE_TYPES,
            'priorities' => self::PRIORITIES,
            'statuses' => self::STATUSES,
            'zones' => self::ZONES,
            'technicians' => $this->getAssignableTechnicians(),
            'canManageAll' => $this->isAdmin(),
            'openRequestsByStore' => $this->openRequestsByStore($technicalRequest->id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $req = TechnicalRequest::findOrFail($id);
        $this->authorizeRequestAccess($req);
        abort_unless($this->canEditTechnicalRequest($req), 403);
        $this->validateFiles($request);

        if ($this->isAdmin()) {
            $data = $this->validateTechnicalRequest($request);
            $data = $this->normalizeTechnicalRequestData($data);
            $data['updated_by'] = auth()->id();

            if (
                (
                    !$request->exists('machine_id') ||
                    $request->input('machine_id') === null ||
                    $request->input('machine_id') === ''
                ) &&
                (string) $request->input('store_id') === (string) $req->store_id
            ) {
                $data['machine_id'] = $req->machine_id;
            }
        } else {
            $data = $request->validate([
                'estado' => 'required|in:' . implode(',', array_keys(self::STATUSES)),
                'data_agendamento' => 'nullable|required_if:estado,agendado|date',
                'data_resolucao' => 'nullable|required_if:estado,concluido|date',
                'observacoes' => 'nullable|string',
            ]);

            $data['updated_by'] = auth()->id();
            $data['observacoes'] = $data['observacoes'] ?? null;
            $data['data_agendamento'] = $data['estado'] === 'agendado'
                ? ($data['data_agendamento'] ?? $req->data_agendamento)
                : null;
            $data['data_resolucao'] = $data['estado'] === 'concluido'
                ? ($data['data_resolucao'] ?? Carbon::now()->toDateTimeString())
                : null;
        }

        $req->update($data);
        $this->storeFiles($request, $req);

        $returnUrl = $this->technicalRequestReturnUrl($request);

        return ($returnUrl
                ? redirect()->to($returnUrl)
                : redirect()->route('backoffice.technical_requests.index'))
            ->with('success', 'Pedido atualizado com sucesso!');
    }

    public function deleteFile(TechnicalRequestFile $file)
    {
        $this->authorizeRequestAccess($file->technicalRequest);
        abort_unless($this->canEditTechnicalRequest($file->technicalRequest), 403);

        Storage::disk('public')->delete($file->file_path);
        $file->delete();

        return back()->with('success', 'Ficheiro apagado com sucesso!');
    }

    public function delete(Request $request, $id)
    {
        abort_unless($this->isAdmin(), 403);

        $req = TechnicalRequest::findOrFail($id);
        $req->delete();

        // 🔎 Guardar filtros e página atuais para manter no redirect
        $queryParams = $request->except(['_token', '_method']);

        return redirect()
            ->route('backoffice.technical_requests.index', $queryParams)
            ->with('success', 'Pedido apagado com sucesso!');
    }


    public function export()
    {
        abort_unless($this->isAdmin(), 403);

        return Excel::download(
            new TechnicalRequestsExport(null, request()->only([
                'q',
                'codigo_loja',
                'serial_number',
                'estado',
                'prioridade',
                'zona',
                'tipo_servico',
                'assigned_technician_id',
                'open_only',
                'mes',
                'data_inicio',
                'data_fim',
            ])),
            'pedidos_assistencia.xlsx'
        );
    }

    public function technicians(Request $request)
    {
        abort_unless($this->isAdmin(), 403);

        $statuses = array_keys(self::STATUSES);
        $technicians = $this->getAssignableTechnicians();
        $query = TechnicalRequest::with('assignedTechnician');
        $this->applyDateFilters($query, $request);
        $requests = $query->get();

        $technicianStats = $technicians
            ->map(function ($technician) use ($requests, $statuses) {
                $technicianRequests = $requests->where('assigned_technician_id', $technician->id);
                $stateCounts = collect($statuses)->mapWithKeys(function ($status) use ($technicianRequests) {
                    return [$status => $technicianRequests->where('estado', $status)->count()];
                });

                return [
                    'technician' => $technician,
                    'total' => $technicianRequests->count(),
                    'active_total' => $technicianRequests->where('estado', '!=', 'concluido')->count(),
                    'states' => $stateCounts,
                ];
            })
            ->filter(fn ($item) => $item['total'] > 0)
            ->values();

        $unassignedRequests = $requests->whereNull('assigned_technician_id');
        $unassignedStats = [
            'technician' => null,
            'label' => 'Por atribuir',
            'email' => null,
            'total' => $unassignedRequests->count(),
            'states' => collect($statuses)->mapWithKeys(function ($status) use ($unassignedRequests) {
                return [$status => $unassignedRequests->where('estado', $status)->count()];
            }),
        ];

        $summary = [
            'technicians' => $technicianStats->count(),
            'assigned_requests' => $technicianStats->sum('total'),
            'unassigned_requests' => $unassignedStats['total'],
            'pending' => $technicianStats->sum(fn ($item) => $item['states']['pendente'] ?? 0),
            'scheduled' => $technicianStats->sum(fn ($item) => $item['states']['agendado'] ?? 0),
            'awaiting_part' => $technicianStats->sum(fn ($item) => $item['states']['aguarda_peca'] ?? 0),
            'completed' => $technicianStats->sum(fn ($item) => $item['states']['concluido'] ?? 0),
        ];

        return view('backoffice.technical_requests.technicians', [
            'technicianStats' => $technicianStats,
            'unassignedStats' => $unassignedStats,
            'statuses' => self::STATUSES,
            'summary' => $summary,
        ]);
    }

    public function openByTechnician(Request $request, $id)
    {
        abort_unless($this->isAdmin(), 403);

        $technician = User::query()
            ->whereNull('deleted_at')
            ->findOrFail($id);

        $query = TechnicalRequest::with(['store', 'machine', 'assignedTechnician'])
            ->where('assigned_technician_id', $technician->id)
            ->where('estado', '!=', 'concluido')
            ->orderByRaw("FIELD(estado, 'pendente', 'agendado', 'aguarda_peca', 'cancelado', 'concluido')")
            ->orderBy('data_pedido', 'desc');

        $this->applyDateFilters($query, $request);
        $statsQuery = clone $query;
        $selectedStatus = $this->selectedOpenStatus($request);

        if ($selectedStatus) {
            $query->where('estado', $selectedStatus);
        }

        return view('backoffice.technical_requests.open_by_technician', [
            'technician' => $technician,
            'requests' => $query->get(),
            'openStats' => $this->openStatusStats($statsQuery),
            'selectedStatus' => $selectedStatus,
            'statuses' => self::STATUSES,
            'canExport' => true,
            'backRoute' => route('backoffice.technical_requests.technicians', $request->only(['mes', 'data_inicio', 'data_fim'])),
        ]);
    }

    public function myOpenRequests(Request $request)
    {
        $technician = auth()->user();

        $query = TechnicalRequest::with(['store', 'machine', 'assignedTechnician'])
            ->where('assigned_technician_id', $technician->id)
            ->where('estado', '!=', 'concluido')
            ->orderByRaw("FIELD(estado, 'pendente', 'agendado', 'aguarda_peca', 'cancelado', 'concluido')")
            ->orderBy('data_pedido', 'desc');

        $this->applyDateFilters($query, $request);
        $statsQuery = clone $query;
        $selectedStatus = $this->selectedOpenStatus($request);

        if ($selectedStatus) {
            $query->where('estado', $selectedStatus);
        }

        return view('backoffice.technical_requests.open_by_technician', [
            'technician' => $technician,
            'requests' => $query->get(),
            'openStats' => $this->openStatusStats($statsQuery),
            'selectedStatus' => $selectedStatus,
            'statuses' => self::STATUSES,
            'canExport' => false,
            'backRoute' => route('backoffice.technical_requests.index', $request->only(['mes', 'data_inicio', 'data_fim'])),
        ]);
    }

    public function openAllTechnicians(Request $request)
    {
        return $this->openAllRequestsView($request, false);
    }

    public function openAllRequests(Request $request)
    {
        return $this->openAllRequestsView($request, true);
    }

    private function openAllRequestsView(Request $request, bool $includeUnassigned)
    {
        abort_unless($this->isAdmin(), 403);

        $query = TechnicalRequest::with(['store', 'machine', 'assignedTechnician'])
            ->where('estado', '!=', 'concluido')
            ->orderByRaw("FIELD(estado, 'pendente', 'agendado', 'aguarda_peca', 'cancelado', 'concluido')")
            ->orderBy('data_pedido', 'desc');

        if (!$includeUnassigned) {
            $query->whereNotNull('assigned_technician_id');
        }

        $this->applyDateFilters($query, $request);
        $requests = $query->get();
        $requestsByTechnician = $requests
            ->groupBy('assigned_technician_id')
            ->sortBy(function ($technicianRequests) {
                $technician = $technicianRequests->first()->assignedTechnician;

                return mb_strtolower($technician->name ?? $technician->email ?? '');
            });

        return view('backoffice.technical_requests.open_all_technicians', [
            'requests' => $requests,
            'requestsByTechnician' => $requestsByTechnician,
            'openSummary' => [
                'technicians' => $requests->whereNotNull('assigned_technician_id')->pluck('assigned_technician_id')->unique()->count(),
                'unassigned' => $requests->whereNull('assigned_technician_id')->count(),
                'pending' => $requests->where('estado', 'pendente')->count(),
                'scheduled' => $requests->where('estado', 'agendado')->count(),
                'awaiting_part' => $requests->where('estado', 'aguarda_peca')->count(),
                'cancelled' => $requests->where('estado', 'cancelado')->count(),
            ],
            'statuses' => self::STATUSES,
            'includeUnassigned' => $includeUnassigned,
            'technicians' => $includeUnassigned ? $this->getAssignableTechnicians() : collect(),
            'backRoute' => $includeUnassigned
                ? route('backoffice.technical_requests.index', $request->only(['mes', 'data_inicio', 'data_fim']))
                : route('backoffice.technical_requests.technicians', $request->only(['mes', 'data_inicio', 'data_fim'])),
        ]);
    }

    public function assignTechnician(Request $request, $id)
    {
        abort_unless($this->isAdmin(), 403);

        $data = $request->validate([
            'assigned_technician_id' => 'nullable|exists:users,id',
        ]);

        $technicalRequest = TechnicalRequest::findOrFail($id);
        $technicalRequest->update([
            'assigned_technician_id' => $data['assigned_technician_id'] ?? null,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Técnico do pedido atualizado com sucesso!');
    }

    public function exportByTechnician($id)
    {
        abort_unless($this->isAdmin(), 403);

        $technician = User::query()
            ->whereNull('deleted_at')
            ->findOrFail($id);

        $filename = 'pedidos_' . str($technician->name ?: $technician->email)
            ->lower()
            ->slug('_')
            ->append('.xlsx')
            ->value();

        return Excel::download(
            new TechnicalRequestsExport($technician, request()->only([
                'q',
                'codigo_loja',
                'serial_number',
                'estado',
                'prioridade',
                'zona',
                'tipo_servico',
                'assigned_technician_id',
                'mes',
                'data_inicio',
                'data_fim',
            ])),
            $filename
        );
    }

    private function validateTechnicalRequest(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|exists:stores,id',
            'machine_id' => [
                'nullable',
                Rule::exists('machines', 'id')->where(function ($query) use ($request) {
                    $query->where('store_id', $request->store_id);
                }),
            ],
            'assigned_technician_id' => 'nullable|exists:users,id',
            'origem' => 'required|string|max:255',
            'tipo_servico' => 'required|in:' . implode(',', array_keys(self::SERVICE_TYPES)),
            'zona' => 'nullable|in:' . implode(',', array_keys(self::ZONES)),
            'descricao_problema' => 'nullable|string',
            'prioridade' => 'required|in:' . implode(',', array_keys(self::PRIORITIES)),
            'estado' => 'required|in:' . implode(',', array_keys(self::STATUSES)),
            'data_pedido' => 'required|date',
            'data_agendamento' => 'nullable|date',
            'data_resolucao' => 'nullable|date',
            'observacoes' => 'nullable|string',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (
                $request->input('estado') !== 'concluido' ||
                !$request->filled('data_resolucao') ||
                !$request->filled('data_pedido')
            ) {
                return;
            }

            $dataPedido = Carbon::parse($request->input('data_pedido'))->startOfDay();
            $dataResolucao = Carbon::parse($request->input('data_resolucao'));

            if ($dataResolucao->lt($dataPedido)) {
                $validator->errors()->add(
                    'data_resolucao',
                    __('A data da resolução não pode ser anterior à data do pedido.')
                );
            }
        });

        return $validator->validate();
    }

    private function normalizeTechnicalRequestData(array $data): array
    {
        $data['data_agendamento'] = $data['data_agendamento'] ?? null;
        $data['data_resolucao'] = $data['data_resolucao'] ?? null;
        $data['machine_id'] = $data['machine_id'] ?? null;
        $data['assigned_technician_id'] = $data['assigned_technician_id'] ?? null;
        $data['zona'] = $data['zona'] ?? null;
        $data['descricao_problema'] = $data['descricao_problema'] ?? '';
        $data['observacoes'] = $data['observacoes'] ?? null;

        if (($data['estado'] ?? null) !== 'agendado') {
            $data['data_agendamento'] = null;
        }

        if (($data['estado'] ?? null) !== 'concluido') {
            $data['data_resolucao'] = null;
        } elseif (!empty($data['data_resolucao']) && strlen($data['data_resolucao']) === 10) {
            $data['data_resolucao'] = Carbon::parse($data['data_resolucao'])->endOfDay()->toDateTimeString();
        }

        return $data;
    }

    private function validateFiles(Request $request): void
    {
        if (!$request->hasFile('files')) {
            return;
        }

        $request->validate([
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png,gif,webp|max:10240',
        ]);
    }

    private function storeFiles(Request $request, TechnicalRequest $technicalRequest): void
    {
        if (!$request->hasFile('files')) {
            return;
        }

        foreach ($request->file('files') as $file) {
            $path = $file->store('technical_request_files', 'public');

            $technicalRequest->files()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
            ]);
        }
    }

    private function openRequestsByStore(?int $excludeRequestId = null)
    {
        return TechnicalRequest::with(['assignedTechnician'])
            ->when($excludeRequestId, function ($query) use ($excludeRequestId) {
                $query->where('id', '!=', $excludeRequestId);
            })
            ->whereNotIn('estado', ['concluido', 'cancelado'])
            ->orderBy('data_pedido', 'desc')
            ->get()
            ->groupBy('store_id')
            ->map(function ($requests) {
                return $requests->map(function (TechnicalRequest $request) {
                    return [
                        'id' => $request->id,
                        'estado' => self::STATUSES[$request->estado] ?? ucfirst(str_replace('_', ' ', $request->estado)),
                        'prioridade' => ucfirst($request->prioridade ?? ''),
                        'tecnico' => $request->assignedPersonLabel(),
                        'data_pedido' => $request->data_pedido ? Carbon::parse($request->data_pedido)->format('d/m/Y') : '-',
                        'descricao' => str($request->descricao_problema ?: 'Sem descrição.')->limit(90)->value(),
                        'url' => route('backoffice.technical_requests.show', $request->id),
                    ];
                })->values();
            });
    }

    private function getAssignableTechnicians()
    {
        return User::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function applyDateFilters($query, Request $request): void
    {
        if ($request->filled('mes')) {
            $monthDate = Carbon::createFromFormat('Y-m', $request->mes);
            $query->whereBetween('data_pedido', [
                $monthDate->copy()->startOfMonth()->toDateString(),
                $monthDate->copy()->endOfMonth()->toDateString(),
            ]);
            return;
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_pedido', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_pedido', '<=', $request->data_fim);
        }
    }

    private function selectedOpenStatus(Request $request): ?string
    {
        $status = $request->input('estado');

        if (!$status || $status === 'concluido' || !array_key_exists($status, self::STATUSES)) {
            return null;
        }

        return $status;
    }

    private function openStatusStats($query): array
    {
        $requests = $query->get(['estado']);

        return [
            'total' => $requests->count(),
            'pendente' => $requests->where('estado', 'pendente')->count(),
            'agendado' => $requests->where('estado', 'agendado')->count(),
            'aguarda_peca' => $requests->where('estado', 'aguarda_peca')->count(),
        ];
    }

    private function isAdmin(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->hasRole('admin')
            || $user->hasRole('administrator')
            || $user->hasRole('administrador');
    }

    private function authorizeRequestAccess(TechnicalRequest $technicalRequest): void
    {
        if ($this->isAdmin()) {
            return;
        }

        abort_unless((int) $technicalRequest->assigned_technician_id === (int) auth()->id(), 403);
    }

    private function canEditTechnicalRequest(TechnicalRequest $technicalRequest): bool
    {
        return $this->isAdmin() || $technicalRequest->estado !== 'concluido';
    }

    private function technicalRequestReturnUrl(Request $request): ?string
    {
        $returnUrl = $request->input('return_url');

        if (!$returnUrl) {
            return null;
        }

        if (str_starts_with($returnUrl, '/backoffice/')) {
            return $returnUrl;
        }

        $urlParts = parse_url($returnUrl);
        $appParts = parse_url(url('/'));

        return ($urlParts['host'] ?? null) === ($appParts['host'] ?? null)
            && str_starts_with($urlParts['path'] ?? '', '/backoffice/')
            ? $returnUrl
            : null;
    }
}
