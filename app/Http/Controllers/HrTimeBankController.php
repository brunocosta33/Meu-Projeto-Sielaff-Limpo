<?php

namespace App\Http\Controllers;

use App\Models\HrStaff;
use App\Models\HrTimeEntry;
use App\Services\HotlineRotation;
use App\Services\HrAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Banco de horas: horas extra, intervenções de piquete, horas em falta e acertos (pagamentos e folgas),
 * mais o subsídio de prevenção de piquete em euros.
 * Gestão de RH vê e gere tudo; os restantes veem o seu saldo e registam as suas horas (ficam pendentes).
 */
class HrTimeBankController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isManager = HrAccess::isManager($user);
        $month = $request->filled('mes') ? Carbon::createFromFormat('Y-m', $request->mes)->startOfMonth() : now()->startOfMonth();
        $types = config('hr.time_entry_types');

        $staff = HrStaff::with('user')->where('ativo', true)->get()->sortBy(fn ($s) => $s->user->name ?? '')->values();
        if (!$isManager) {
            $staff = $staff->where('id', HrAccess::staffOf($user)?->id)->values();
        }
        $filterStaff = $isManager ? $request->input('colaborador') : HrAccess::staffOf($user)?->id;

        // Saldos (todos os movimentos aprovados, desde sempre)
        $approved = HrTimeEntry::whereIn('hr_staff_id', $staff->pluck('id'))->where('estado', 'aprovado')->get()->groupBy('hr_staff_id');
        $pending = HrTimeEntry::whereIn('hr_staff_id', $staff->pluck('id'))->where('estado', 'pendente')->get()->groupBy('hr_staff_id');
        $summary = $staff->map(function ($s) use ($approved, $pending) {
            $entries = $approved->get($s->id, collect());
            $sum = fn ($unit) => $entries->filter(fn ($e) => $e->type['unit'] === $unit)->sum(fn ($e) => $e->signed_amount);

            return (object) [
                'staff' => $s,
                'horas' => round($sum('horas'), 2),
                'euros' => round($sum('euros'), 2),
                'pendentes' => $pending->get($s->id, collect())->count(),
            ];
        });

        // Movimentos do mês
        $entries = HrTimeEntry::with(['staff.user', 'creator', 'decider'])
            ->whereIn('hr_staff_id', $staff->pluck('id'))
            ->whereBetween('data', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->when($filterStaff, fn ($q) => $q->where('hr_staff_id', $filterStaff))
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
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
            'pendingTotal' => $isManager ? HrTimeEntry::where('estado', 'pendente')->count() : 0,
            'prevencaoValor' => (float) config('hr.piquete.prevencao_valor'),
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
            'valor' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'descricao' => ['nullable', 'string', 'max:255'],
        ], [
            'horas.regex' => 'Indique as horas como 2, 2,5 ou 2:30.',
            'tipo.in' => 'Não pode registar este tipo de movimento.',
        ]);

        $staffId = $isManager ? (int) $data['hr_staff_id'] : HrAccess::staffOf($user)?->id;
        abort_unless($staffId, 403);

        $unit = $types[$data['tipo']]['unit'];
        $hours = $unit === 'horas' ? $this->parseHours($data['horas'] ?? '') : 0;
        $value = $unit === 'euros' ? (float) ($data['valor'] ?? 0) : 0;
        if (($unit === 'horas' && $hours <= 0) || ($unit === 'euros' && $value <= 0)) {
            throw ValidationException::withMessages([$unit === 'horas' ? 'horas' : 'valor' => $unit === 'horas' ? 'Indique o número de horas.' : 'Indique o valor em euros.']);
        }

        HrTimeEntry::create([
            'hr_staff_id' => $staffId,
            'data' => $data['data'],
            'tipo' => $data['tipo'],
            'horas' => $hours,
            'valor' => $value,
            'descricao' => $data['descricao'] ?? null,
            'estado' => $isManager ? 'aprovado' : 'pendente',
            'created_by' => $user->id,
            'decided_by' => $isManager ? $user->id : null,
            'decided_at' => $isManager ? now() : null,
        ]);

        flash($isManager ? 'Movimento registado.' : 'Pedido registado. Fica pendente até ser aprovado pela gestão de RH.')->success();

        return redirect()->route('backoffice.hr.timebank.index', ['mes' => Carbon::parse($data['data'])->format('Y-m')]);
    }

    public function decide(Request $request, $id)
    {
        $entry = HrTimeEntry::findOrFail($id);
        $estado = $request->validate(['estado' => ['required', Rule::in(['aprovado', 'rejeitado'])]])['estado'];
        $entry->update(['estado' => $estado, 'decided_by' => auth()->id(), 'decided_at' => now()]);

        flash($estado === 'aprovado' ? 'Movimento aprovado.' : 'Movimento rejeitado.')->success();

        return back();
    }

    public function destroy($id)
    {
        HrTimeEntry::findOrFail($id)->delete();
        flash('Movimento apagado.')->success();

        return back();
    }

    /**
     * Cria o subsídio de prevenção para cada sábado de piquete do mês, a quem fez o turno
     * (tem em conta trocas; ignora sábados sem substituto). Não duplica.
     */
    public function generatePrevencoes(Request $request, HotlineRotation $rotation)
    {
        $month = Carbon::createFromFormat('Y-m', $request->validate(['mes' => ['required', 'date_format:Y-m']])['mes'])->startOfMonth();
        $end = $month->copy()->endOfMonth()->min(now()->startOfDay()); // só sábados já passados ou de hoje
        if ($end->lt($month)) {
            flash('Este mês ainda não tem sábados de piquete realizados.')->warning();
            return back();
        }

        $staffByUser = HrStaff::where('ativo', true)->get()->keyBy('user_id');
        $value = (float) config('hr.piquete.prevencao_valor');
        $created = 0;
        $skipped = [];

        foreach ($rotation->shifts($month, $end, HotlineRotation::staffFor('piquete')) as $shift) {
            if ($shift->uncovered || !$shift->worker_id) {
                continue;
            }
            $worker = $staffByUser->get($shift->worker_id);
            if (!$worker) {
                $skipped[] = $shift->name . ' (' . $shift->date->format('d/m') . ')';
                continue;
            }

            $entry = HrTimeEntry::firstOrCreate(
                ['referencia' => 'prevencao:' . $shift->date->toDateString() . ':' . $shift->staff->id],
                [
                    'hr_staff_id' => $worker->id,
                    'data' => $shift->date->toDateString(),
                    'tipo' => 'prevencao_piquete',
                    'valor' => $value,
                    'descricao' => 'Piquete ' . $shift->date->format('d/m/Y') . ($shift->zona ? ' · ' . $shift->zona : '')
                        . ($worker->id !== $shift->staff->id ? ' (substitui ' . $shift->staff->user->name . ')' : ''),
                    'estado' => 'aprovado',
                    'created_by' => auth()->id(),
                    'decided_by' => auth()->id(),
                    'decided_at' => now(),
                ]
            );
            if ($entry->wasRecentlyCreated) {
                $created++;
            }
        }

        flash($created ? $created . ' prevenção(ões) de piquete registada(s) em ' . $month->translatedFormat('F Y') . '.' : 'Não havia prevenções novas para registar neste mês.')->success();
        if ($skipped) {
            flash('Sem registo de colaborador (não foi criada prevenção): ' . implode(', ', $skipped))->warning();
        }

        return back();
    }

    /** Exportar os movimentos do mês para Excel (CSV com ; e acentos). */
    public function export(Request $request)
    {
        $month = $request->filled('mes') ? Carbon::createFromFormat('Y-m', $request->mes)->startOfMonth() : now()->startOfMonth();
        $entries = HrTimeEntry::with('staff.user')
            ->whereBetween('data', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->where('estado', 'aprovado')
            ->orderBy('hr_staff_id')->orderBy('data')
            ->get();

        $filename = 'banco-de-horas-' . $month->format('Y-m') . '.csv';

        return response()->streamDownload(function () use ($entries) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para o Excel reconhecer acentos
            fputcsv($out, ['Colaborador', 'Data', 'Tipo', 'Horas (+/−)', 'Valor € (+/−)', 'Descrição'], ';');
            foreach ($entries as $e) {
                $isEuros = $e->type['unit'] === 'euros';
                fputcsv($out, [
                    $e->staff->user->name ?? '',
                    $e->data->format('d/m/Y'),
                    $e->type['label'],
                    $isEuros ? '' : number_format($e->signed_amount, 2, ',', ''),
                    $isEuros ? number_format($e->signed_amount, 2, ',', '') : '',
                    $e->descricao,
                ], ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Aceita "2", "2,5", "2.5" ou "2:30". */
    private function parseHours(string $input): float
    {
        $input = trim($input);
        if (str_contains($input, ':')) {
            [$h, $m] = array_map('intval', explode(':', $input) + [1 => 0]);

            return round($h + min($m, 59) / 60, 2);
        }

        return round((float) str_replace(',', '.', $input), 2);
    }
}
