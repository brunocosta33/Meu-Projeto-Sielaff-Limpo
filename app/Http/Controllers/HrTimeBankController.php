<?php

namespace App\Http\Controllers;

use App\Models\HrStaff;
use App\Models\HrTimeEntry;
use App\Models\HrTimeEntryFile;
use App\Services\HotlineRotation;
use App\Services\HrAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Horas e Piquetes: piquetes realizados (calculados a partir da escala) e trabalho suplementar em piquete
 * (pagos em dinheiro), horas extra, horas em falta e acertos (pagamentos e folgas).
 * Gestão de RH vê e gere tudo; os restantes veem o seu saldo e pedem as suas horas extra (ficam pendentes).
 */
class HrTimeBankController extends Controller
{
    // Comprovativos ficam no disco privado (só a própria pessoa e a gestão de RH os abrem)
    private const DISK = 'local';

    public function index(Request $request, HotlineRotation $rotation)
    {
        $user = auth()->user();
        $isManager = HrAccess::isManager($user);
        $month = $request->filled('mes') ? Carbon::createFromFormat('Y-m', $request->mes)->startOfMonth() : now()->startOfMonth();
        $monthRange = [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()];
        $types = config('hr.time_entry_types');

        $staff = HrStaff::with('user')->where('ativo', true)->get()->sortBy(fn ($s) => $s->user->name ?? '')->values();
        if (!$isManager) {
            $staff = $staff->where('id', HrAccess::staffOf($user)?->id)->values();
        }
        $filterStaff = $isManager ? $request->input('colaborador') : HrAccess::staffOf($user)?->id;

        // Saldo de horas (movimentos aprovados, desde sempre; o piquete é pago à parte e não conta)
        $approved = HrTimeEntry::whereIn('hr_staff_id', $staff->pluck('id'))->where('estado', 'aprovado')->get()->groupBy('hr_staff_id');
        $pending = HrTimeEntry::whereIn('hr_staff_id', $staff->pluck('id'))->awaitingDecision()->get()->groupBy('hr_staff_id');
        // Piquetes do mês: só contam os confirmados pela gestão; trabalho suplementar aprovado
        $monthApproved = HrTimeEntry::whereIn('hr_staff_id', $staff->pluck('id'))->where('estado', 'aprovado')
            ->whereIn('tipo', ['piquete_feito', 'piquete'])->whereBetween('data', $monthRange)->get();
        $confirmed = $monthApproved->where('tipo', 'piquete_feito')->groupBy('hr_staff_id');
        $monthSupplementary = $monthApproved->where('tipo', 'piquete')->groupBy('hr_staff_id');

        // Sábados de piquete da escala (já passados) e decisão de cada um
        $slots = $this->piqueteSlots($rotation, $month);
        $toConfirm = collect($slots)->where('decision', null);

        $summary = $staff->map(fn ($s) => (object) [
            'staff' => $s,
            'horas' => round($approved->get($s->id, collect())->filter(fn ($e) => $e->countsInBalance())->sum(fn ($e) => $e->signed_amount), 2),
            'piquetes' => $confirmed->get($s->id, collect())->count(),
            'aConfirmar' => $toConfirm->where('worker_staff_id', $s->id)->count(),
            'suplementar' => round($monthSupplementary->get($s->id, collect())->sum('horas'), 2),
            'pendentes' => $pending->get($s->id, collect())->count(),
        ]);

        // Movimentos do mês
        $entries = HrTimeEntry::with(['staff.user', 'creator', 'decider', 'files'])
            ->whereIn('hr_staff_id', $staff->pluck('id'))
            ->whereBetween('data', $monthRange)
            ->when($filterStaff, fn ($q) => $q->where('hr_staff_id', $filterStaff))
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('estado'), fn ($q) => $request->estado === 'pendente' ? $q->awaitingDecision() : $q->where('estado', $request->estado))
            ->orderByDesc('data')->orderByDesc('id')
            ->get();

        return view('backoffice.hr.time_bank.index', [
            'month' => $month,
            'types' => $types,
            'staff' => $staff,
            'summary' => $summary,
            'entries' => $entries,
            'filterStaff' => $filterStaff,
            'isManager' => $isManager,
            'ownStaff' => HrAccess::staffOf($user),
            'pendingTotal' => $isManager ? HrTimeEntry::awaitingDecision()->count() : 0,
            'slots' => $isManager ? $slots : [],
            'piqueteStaff' => $isManager ? HotlineRotation::staffFor('piquete') : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $isManager = HrAccess::isManager($user);
        $types = config('hr.time_entry_types');
        $allowedTypes = $isManager ? array_keys($types) : array_keys(array_filter($types, fn ($t) => $t['self']));

        $data = $request->validate([
            'hr_staff_id' => [$isManager ? 'required' : 'nullable', 'exists:hr_staff,id'],
            'data' => ['required', 'date'],
            'tipo' => ['required', Rule::in($allowedTypes)],
            'horas' => ['nullable', 'regex:/^\d{1,3}([.,:]\d{1,2})?$/'],
            'descricao' => ['nullable', 'string', 'max:255'],
        ] + self::fileRules(), [
            'horas.regex' => __('Indique as horas como 2, 2,5 ou 2:30.'),
            'tipo.in' => __('Não pode registar este tipo de movimento.'),
        ] + self::fileMessages());

        $staffId = $isManager ? (int) $data['hr_staff_id'] : HrAccess::staffOf($user)?->id;
        abort_unless($staffId, 403);

        $hours = $this->parseHours($data['horas'] ?? '');
        if ($data['tipo'] !== 'piquete_feito' && $hours <= 0) {
            throw ValidationException::withMessages(['horas' => __('Indique o número de horas.')]);
        }

        // Piquete acrescentado à mão pela gestão (fora da escala): um por pessoa e dia
        if ($data['tipo'] === 'piquete_feito') {
            $hours = 0;
            $duplicate = HrTimeEntry::where('hr_staff_id', $staffId)->where('tipo', 'piquete_feito')
                ->whereDate('data', $data['data'])->where('estado', 'aprovado')->exists();
            if ($duplicate) {
                throw ValidationException::withMessages(['data' => __('Esta pessoa já tem um piquete confirmado nesse dia.')]);
            }
        }

        // Trabalho suplementar num dia em que a pessoa não estava de piquete: avisa (não bloqueia)
        if ($data['tipo'] === 'piquete') {
            $date = Carbon::parse($data['data']);
            $onPiquete = HrTimeEntry::where('hr_staff_id', $staffId)->where('tipo', 'piquete_feito')->where('estado', 'aprovado')->whereDate('data', $date)->exists()
                || collect($this->piqueteSlots(app(HotlineRotation::class), $date->copy()->startOfDay(), $date->copy()->startOfDay()))->contains('worker_staff_id', $staffId);
            if (!$onPiquete) {
                flash(__('Atenção: esta pessoa não estava de piquete a :data (nem na escala nem confirmado).', ['data' => $date->format('d/m/Y')]))->warning();
            }
        }

        // Piquete já registado nesse dia: atualiza-o em vez de criar outro
        if ($data['tipo'] === 'piquete') {
            $existing = HrTimeEntry::where('hr_staff_id', $staffId)->where('tipo', 'piquete')
                ->whereDate('data', $data['data'])->where('estado', '!=', 'rejeitado')->first();
            if ($existing) {
                if ($isManager) {
                    $existing->update(['horas' => $hours, 'horas_propostas' => null] + (!empty($data['descricao']) ? ['descricao' => $data['descricao']] : []));
                    flash(__('Trabalho suplementar do piquete de :data atualizado: :horas.', ['data' => $existing->data->format('d/m'), 'horas' => HrTimeEntry::formatHours($hours)]))->success();
                } else {
                    $existing->update(['horas_propostas' => $hours]);
                    flash(__('Trabalho suplementar enviado: :horas. Fica pendente até ser aprovado pela gestão de RH.', ['horas' => HrTimeEntry::formatHours($hours)]))->success();
                }
                $this->storeFiles($request, $existing);

                return redirect()->route('backoffice.hr.timebank.index', ['mes' => $existing->data->format('Y-m')]);
            }
        }

        $entry = HrTimeEntry::create([
            'hr_staff_id' => $staffId,
            'data' => $data['data'],
            'tipo' => $data['tipo'],
            'horas' => $hours,
            'descricao' => $data['descricao'] ?? null,
            'estado' => $isManager ? 'aprovado' : 'pendente',
            'created_by' => $user->id,
            'decided_by' => $isManager ? $user->id : null,
            'decided_at' => $isManager ? now() : null,
        ]);
        $this->storeFiles($request, $entry);

        flash($isManager ? __('Movimento registado.') : __('Horas enviadas para aprovação. Contam para o saldo depois de aprovadas pela gestão de RH.'))->success();

        return redirect()->route('backoffice.hr.timebank.index', ['mes' => Carbon::parse($data['data'])->format('Y-m')]);
    }

    /** Alterar as horas (ex.: trabalho suplementar de um piquete) e a descrição. */
    public function update(Request $request, $id)
    {
        $entry = HrTimeEntry::findOrFail($id);
        $data = $request->validate([
            'horas' => ['nullable', 'regex:/^\d{1,3}([.,:]\d{1,2})?$/'],
            'descricao' => ['nullable', 'string', 'max:255'],
        ], ['horas.regex' => __('Indique as horas como 2, 2,5 ou 2:30.')]);

        $hours = $this->parseHours($data['horas'] ?? '');
        if ($entry->tipo !== 'piquete' && $hours <= 0) {
            throw ValidationException::withMessages(['horas' => __('Indique o número de horas.')]);
        }

        $entry->update(['horas' => $hours] + ($request->has('descricao') ? ['descricao' => $data['descricao']] : []));
        flash($entry->tipo === 'piquete'
            ? __('Trabalho suplementar do piquete de :data: :horas.', ['data' => $entry->data->format('d/m'), 'horas' => HrTimeEntry::formatHours($hours)])
            : __('Movimento atualizado.'))->success();

        return back();
    }

    public function decide(Request $request, $id)
    {
        $entry = HrTimeEntry::findOrFail($id);
        $estado = $request->validate(['estado' => ['required', Rule::in(['aprovado', 'rejeitado'])]])['estado'];

        // Proposta de trabalho suplementar num piquete já aprovado: só mexe nas horas
        if ($entry->hasProposal() && $entry->estado === 'aprovado') {
            $entry->update(($estado === 'aprovado' ? ['horas' => $entry->horas_propostas] : [])
                + ['horas_propostas' => null, 'decided_by' => auth()->id(), 'decided_at' => now()]);
            flash($estado === 'aprovado' ? __('Trabalho suplementar aprovado.') : __('Trabalho suplementar rejeitado.'))->success();

            return back();
        }

        $entry->update(['estado' => $estado, 'horas_propostas' => null, 'decided_by' => auth()->id(), 'decided_at' => now()]);

        flash($estado === 'aprovado' ? __('Movimento aprovado.') : __('Movimento rejeitado.'))->success();

        return back();
    }

    public function destroy($id)
    {
        $entry = HrTimeEntry::with('files')->findOrFail($id);
        foreach ($entry->files as $file) {
            Storage::disk(self::DISK)->delete($file->path);
        }
        $entry->delete();
        flash(__('Movimento apagado.'))->success();

        return back();
    }

    /**
     * Sábados de piquete da escala (só os já passados), com quem estava previsto e a decisão da gestão:
     * decision = null (a confirmar), entrada aprovada (confirmado) ou rejeitada (não fez).
     */
    private function piqueteSlots(HotlineRotation $rotation, Carbon $from, ?Carbon $to = null): array
    {
        $to = ($to ?? $from->copy()->endOfMonth())->copy()->min(now()->startOfDay());
        if ($to->lt($from)) {
            return [];
        }

        $staffByUser = HrStaff::where('ativo', true)->pluck('id', 'user_id');
        $shifts = $rotation->shifts($from->copy(), $to, HotlineRotation::staffFor('piquete'));
        $decisions = HrTimeEntry::with('staff.user')->where('tipo', 'piquete_feito')
            ->whereIn('referencia', $shifts->map(fn ($sh) => self::slotRef($sh->date, $sh->staff->id)))
            ->get()->keyBy('referencia');

        return $shifts->map(fn ($sh) => (object) [
            'date' => $sh->date,
            'zona' => $sh->zona,
            'slot_staff' => $sh->staff,
            'worker_name' => $sh->name,
            'worker_staff_id' => $sh->worker_id ? ($staffByUser[$sh->worker_id] ?? null) : null,
            'uncovered' => $sh->uncovered,
            'note' => $sh->override_note,
            'ref' => self::slotRef($sh->date, $sh->staff->id),
            'decision' => $decisions->get(self::slotRef($sh->date, $sh->staff->id)),
        ])->sortBy(fn ($slot) => $slot->date->toDateString() . $slot->zona)->values()->all();
    }

    private static function slotRef(Carbon $date, int $slotStaffId): string
    {
        return 'piquete:' . $date->toDateString() . ':' . $slotStaffId;
    }

    /** Confirmar (com a pessoa que o fez) ou marcar como não feito um sábado de piquete da escala. */
    public function confirmPiquete(Request $request)
    {
        $data = $request->validate([
            'data' => ['required', 'date'],
            'slot_staff_id' => ['required', 'exists:hr_staff,id'],
            'hr_staff_id' => ['nullable', 'required_if:acao,confirmar', 'exists:hr_staff,id'],
            'acao' => ['required', Rule::in(['confirmar', 'recusar'])],
        ], ['hr_staff_id.required_if' => __('Escolha quem fez o piquete.')]);

        $date = Carbon::parse($data['data']);
        $slotStaff = HrStaff::with('user')->findOrFail($data['slot_staff_id']);
        $confirm = $data['acao'] === 'confirmar';
        $worker = $confirm ? HrStaff::with('user')->findOrFail($data['hr_staff_id']) : $slotStaff;

        HrTimeEntry::updateOrCreate(
            ['referencia' => self::slotRef($date, $slotStaff->id)],
            [
                'hr_staff_id' => $worker->id,
                'data' => $date->toDateString(),
                'tipo' => 'piquete_feito',
                'horas' => 0,
                'descricao' => 'Piquete ' . $date->format('d/m/Y') . ($slotStaff->zona ? ' · ' . config('hr.zones.' . $slotStaff->zona) : '')
                    . ($worker->id !== $slotStaff->id ? ' (no lugar de ' . $slotStaff->user->name . ')' : ''),
                'estado' => $confirm ? 'aprovado' : 'rejeitado',
                'created_by' => auth()->id(),
                'decided_by' => auth()->id(),
                'decided_at' => now(),
            ]
        );

        flash($confirm
            ? __('Piquete de :data confirmado: :nome.', ['data' => $date->format('d/m'), 'nome' => $worker->user->name])
            : __('Piquete de :data marcado como não feito.', ['data' => $date->format('d/m')]))->success();

        return back();
    }

    /** Exportar os movimentos aprovados do mês para Excel (CSV com ; e acentos). */
    public function export(Request $request)
    {
        $month = $request->filled('mes') ? Carbon::createFromFormat('Y-m', $request->mes)->startOfMonth() : now()->startOfMonth();

        $entries = HrTimeEntry::with('staff.user')
            ->whereBetween('data', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->where('estado', 'aprovado')
            ->orderBy('hr_staff_id')->orderBy('data')
            ->get();

        $filename = 'horas-e-piquetes-' . $month->format('Y-m') . '.csv';

        return response()->streamDownload(function () use ($entries) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para o Excel reconhecer acentos
            fputcsv($out, [__('Colaborador'), __('Data'), __('Tipo'), __('Piquete (a pagar)'), __('Trabalho suplementar em piquete (h, a pagar)'), __('Saldo de horas (+/−)'), __('Descrição')], ';');
            foreach ($entries as $e) {
                $isPiquete = !$e->countsInBalance();
                fputcsv($out, [
                    $e->staff->user->name ?? '',
                    $e->data->format('d/m/Y'),
                    $e->type['label'],
                    $e->tipo === 'piquete_feito' ? 1 : '',
                    $e->tipo === 'piquete' ? number_format($e->horas, 2, ',', '') : '',
                    $isPiquete ? '' : number_format($e->signed_amount, 2, ',', ''),
                    $e->descricao,
                ], ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Juntar comprovativos a um movimento (a própria pessoa ou a gestão de RH). */
    public function attach(Request $request, $id)
    {
        $entry = HrTimeEntry::findOrFail($id);
        $this->authorizeEntry($entry);
        $request->validate(['anexos' => ['required', 'array', 'max:10'], 'anexos.*' => self::fileRules()['anexos.*']], self::fileMessages() + [
            'anexos.required' => __('Escolha pelo menos uma fotografia ou documento.'),
        ]);

        $n = $this->storeFiles($request, $entry);
        flash($n === 1 ? __('Comprovativo anexado.') : __(':total comprovativos anexados.', ['total' => $n]))->success();

        return back();
    }

    public function showFile($fileId)
    {
        $file = HrTimeEntryFile::with('entry')->findOrFail($fileId);
        $this->authorizeEntry($file->entry);
        abort_unless(Storage::disk(self::DISK)->exists($file->path), 404);

        return Storage::disk(self::DISK)->response($file->path, $file->nome);
    }

    public function deleteFile($fileId)
    {
        $file = HrTimeEntryFile::with('entry')->findOrFail($fileId);
        abort_unless(HrAccess::isManager(auth()->user()) || $file->uploaded_by === auth()->id(), 403);
        Storage::disk(self::DISK)->delete($file->path);
        $file->delete();
        flash(__('Comprovativo removido.'))->success();

        return back();
    }

    private function authorizeEntry(HrTimeEntry $entry): void
    {
        abort_unless(HrAccess::isManager(auth()->user()) || $entry->hr_staff_id === HrAccess::staffOf(auth()->user())?->id, 403);
    }

    private static function fileRules(): array
    {
        return [
            'anexos' => ['nullable', 'array', 'max:10'],
            'anexos.*' => ['file', 'mimes:jpg,jpeg,png,heic,heif,webp,pdf', 'max:32768'],
        ];
    }

    private static function fileMessages(): array
    {
        return [
            'anexos.max' => __('No máximo 10 comprovativos de cada vez.'),
            'anexos.*.mimes' => __('Os comprovativos têm de ser fotografias (JPG, PNG, HEIC) ou PDF.'),
            'anexos.*.max' => __('Cada comprovativo pode ter no máximo 32 MB.'),
        ];
    }

    private function storeFiles(Request $request, HrTimeEntry $entry): int
    {
        $count = 0;
        foreach ((array) $request->file('anexos', []) as $file) {
            $entry->files()->create([
                'path' => $file->store('time_bank_proofs', self::DISK),
                'nome' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'tamanho' => $file->getSize(),
                'uploaded_by' => auth()->id(),
            ]);
            $count++;
        }

        return $count;
    }

    /** Aceita "2", "2,5", "2.5" ou "2:30". */
    private function parseHours(string $input): float
    {
        $input = trim($input);
        if ($input === '') {
            return 0;
        }
        if (str_contains($input, ':')) {
            [$h, $m] = array_map('intval', explode(':', $input) + [1 => 0]);

            return round($h + min($m, 59) / 60, 2);
        }

        return round((float) str_replace(',', '.', $input), 2);
    }
}
