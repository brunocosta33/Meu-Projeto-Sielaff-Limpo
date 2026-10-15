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
        // Piquetes do mês: realizados (escala) e trabalho suplementar aprovado
        $piquetesDone = $this->piquetesDone($rotation, $month);
        $monthSupplementary = HrTimeEntry::whereIn('hr_staff_id', $staff->pluck('id'))->where('tipo', 'piquete')->where('estado', 'aprovado')
            ->whereBetween('data', $monthRange)->get()->groupBy('hr_staff_id');

        $summary = $staff->map(fn ($s) => (object) [
            'staff' => $s,
            'horas' => round($approved->get($s->id, collect())->filter(fn ($e) => $e->countsInBalance())->sum(fn ($e) => $e->signed_amount), 2),
            'piquetes' => count($piquetesDone[$s->id] ?? []),
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
            'horas.regex' => 'Indique as horas como 2, 2,5 ou 2:30.',
            'tipo.in' => 'Não pode registar este tipo de movimento.',
        ] + self::fileMessages());

        $staffId = $isManager ? (int) $data['hr_staff_id'] : HrAccess::staffOf($user)?->id;
        abort_unless($staffId, 403);

        $hours = $this->parseHours($data['horas'] ?? '');
        if ($hours <= 0) {
            throw ValidationException::withMessages(['horas' => 'Indique o número de horas.']);
        }

        // Trabalho suplementar num dia em que a pessoa não estava de piquete: avisa (não bloqueia)
        if ($data['tipo'] === 'piquete') {
            $date = Carbon::parse($data['data']);
            $done = $this->piquetesDone(app(HotlineRotation::class), $date->copy()->startOfDay(), $date->copy()->startOfDay());
            if (!in_array($date->toDateString(), $done[$staffId] ?? [], true)) {
                flash('Atenção: segundo a escala, esta pessoa não estava de piquete a ' . $date->format('d/m/Y') . '.')->warning();
            }
        }

        // Piquete já registado nesse dia: atualiza-o em vez de criar outro
        if ($data['tipo'] === 'piquete') {
            $existing = HrTimeEntry::where('hr_staff_id', $staffId)->where('tipo', 'piquete')
                ->whereDate('data', $data['data'])->where('estado', '!=', 'rejeitado')->first();
            if ($existing) {
                if ($isManager) {
                    $existing->update(['horas' => $hours, 'horas_propostas' => null] + (!empty($data['descricao']) ? ['descricao' => $data['descricao']] : []));
                    flash('Trabalho suplementar do piquete de ' . $existing->data->format('d/m') . ' atualizado: ' . HrTimeEntry::formatHours($hours) . '.')->success();
                } else {
                    $existing->update(['horas_propostas' => $hours]);
                    flash('Trabalho suplementar enviado: ' . HrTimeEntry::formatHours($hours) . '. Fica pendente até ser aprovado pela gestão de RH.')->success();
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

        flash($isManager ? 'Movimento registado.' : 'Horas enviadas para aprovação. Contam para o saldo depois de aprovadas pela gestão de RH.')->success();

        return redirect()->route('backoffice.hr.timebank.index', ['mes' => Carbon::parse($data['data'])->format('Y-m')]);
    }

    /** Alterar as horas (ex.: trabalho suplementar de um piquete) e a descrição. */
    public function update(Request $request, $id)
    {
        $entry = HrTimeEntry::findOrFail($id);
        $data = $request->validate([
            'horas' => ['nullable', 'regex:/^\d{1,3}([.,:]\d{1,2})?$/'],
            'descricao' => ['nullable', 'string', 'max:255'],
        ], ['horas.regex' => 'Indique as horas como 2, 2,5 ou 2:30.']);

        $hours = $this->parseHours($data['horas'] ?? '');
        if ($entry->tipo !== 'piquete' && $hours <= 0) {
            throw ValidationException::withMessages(['horas' => 'Indique o número de horas.']);
        }

        $entry->update(['horas' => $hours] + ($request->has('descricao') ? ['descricao' => $data['descricao']] : []));
        flash($entry->tipo === 'piquete'
            ? 'Trabalho suplementar do piquete de ' . $entry->data->format('d/m') . ': ' . HrTimeEntry::formatHours($hours) . '.'
            : 'Movimento atualizado.')->success();

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
            flash($estado === 'aprovado' ? 'Trabalho suplementar aprovado.' : 'Trabalho suplementar rejeitado.')->success();

            return back();
        }

        $entry->update(['estado' => $estado, 'horas_propostas' => null, 'decided_by' => auth()->id(), 'decided_at' => now()]);

        flash($estado === 'aprovado' ? 'Movimento aprovado.' : 'Movimento rejeitado.')->success();

        return back();
    }

    public function destroy($id)
    {
        $entry = HrTimeEntry::with('files')->findOrFail($id);
        foreach ($entry->files as $file) {
            Storage::disk(self::DISK)->delete($file->path);
        }
        $entry->delete();
        flash('Movimento apagado.')->success();

        return back();
    }

    /**
     * Sábados de piquete realizados, por colaborador (id) → lista de datas, calculados a partir da escala:
     * conta quem fez de facto o turno (trocas incluídas) e ignora sábados sem substituto e ainda por acontecer.
     */
    private function piquetesDone(HotlineRotation $rotation, Carbon $from, ?Carbon $to = null): array
    {
        $to = ($to ?? $from->copy()->endOfMonth())->copy()->min(now()->startOfDay());
        if ($to->lt($from)) {
            return [];
        }

        $staffByUser = HrStaff::where('ativo', true)->pluck('id', 'user_id');
        $done = [];
        foreach ($rotation->shifts($from->copy(), $to, HotlineRotation::staffFor('piquete')) as $shift) {
            if ($shift->uncovered || !$shift->worker_id || !isset($staffByUser[$shift->worker_id])) {
                continue;
            }
            $done[$staffByUser[$shift->worker_id]][] = $shift->date->toDateString();
        }

        return $done;
    }

    /** Exportar os movimentos aprovados do mês para Excel (CSV com ; e acentos). */
    public function export(Request $request, HotlineRotation $rotation)
    {
        $month = $request->filled('mes') ? Carbon::createFromFormat('Y-m', $request->mes)->startOfMonth() : now()->startOfMonth();
        $names = HrStaff::with('user')->get()->mapWithKeys(fn ($s) => [$s->id => $s->user->name ?? '']);
        $piquetes = collect($this->piquetesDone($rotation, $month))
            ->flatMap(fn ($dates, $staffId) => collect($dates)->map(fn ($d) => [$names[$staffId] ?? '', $d]))
            ->sortBy(fn ($r) => $r[0] . $r[1])->values();
        $entries = HrTimeEntry::with('staff.user')
            ->whereBetween('data', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->where('estado', 'aprovado')
            ->orderBy('hr_staff_id')->orderBy('data')
            ->get();

        $filename = 'horas-e-piquetes-' . $month->format('Y-m') . '.csv';

        return response()->streamDownload(function () use ($entries, $piquetes) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para o Excel reconhecer acentos
            fputcsv($out, ['Colaborador', 'Data', 'Tipo', 'Piquete (a pagar)', 'Trabalho suplementar em piquete (h, a pagar)', 'Saldo de horas (+/−)', 'Descrição'], ';');
            foreach ($piquetes as [$name, $date]) {
                fputcsv($out, [$name, Carbon::parse($date)->format('d/m/Y'), 'Piquete realizado', 1, '', '', 'Segundo a escala de piquete'], ';');
            }
            foreach ($entries as $e) {
                $isPiquete = !$e->countsInBalance();
                fputcsv($out, [
                    $e->staff->user->name ?? '',
                    $e->data->format('d/m/Y'),
                    $e->type['label'],
                    '',
                    $isPiquete ? number_format($e->horas, 2, ',', '') : '',
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
            'anexos.required' => 'Escolha pelo menos uma fotografia ou documento.',
        ]);

        $n = $this->storeFiles($request, $entry);
        flash($n === 1 ? 'Comprovativo anexado.' : $n . ' comprovativos anexados.')->success();

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
        flash('Comprovativo removido.')->success();

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
            'anexos.max' => 'No máximo 10 comprovativos de cada vez.',
            'anexos.*.mimes' => 'Os comprovativos têm de ser fotografias (JPG, PNG, HEIC) ou PDF.',
            'anexos.*.max' => 'Cada comprovativo pode ter no máximo 32 MB.',
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
