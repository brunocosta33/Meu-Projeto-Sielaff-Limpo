<?php

namespace App\Http\Controllers;

use App\Models\HrAbsence;
use App\Models\HrStaff;
use App\Models\HrVacation;
use App\Services\HrAccess;
use App\Services\WorkingDays;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HrAbsenceController extends Controller
{
    // Comprovativos (podem ser atestados médicos) ficam no disco privado
    private const DISK = 'local';

    public function __construct(private WorkingDays $workingDays)
    {
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $staff = HrStaff::with('user')->findOrFail($data['hr_staff_id']);

        $absence = new HrAbsence($data + ['created_by' => auth()->id()]);
        $this->checkOverlaps($absence, $staff);
        $this->applyDecision($absence, $data['situacao']);
        $absence->save();
        $this->storeDocument($request, $absence);

        flash('Falta de ' . $staff->user->name . ' registada (' . $absence->duracao . ', ' . mb_strtolower($absence->situacao_label) . ').')->success();

        return redirect()->route('backoffice.hr.vacations.index', ['ano' => $absence->data_inicio->year]);
    }

    public function edit($id)
    {
        $absence = HrAbsence::with(['staff.user', 'creator', 'decider'])->findOrFail($id);
        $staff = HrStaff::with('user')->where('ativo', true)->get()->sortBy(fn ($s) => $s->user->name ?? '');

        return view('backoffice.hr.absences.edit', compact('absence', 'staff'));
    }

    public function update(Request $request, $id)
    {
        $absence = HrAbsence::findOrFail($id);
        $data = $this->validated($request);
        $staff = HrStaff::with('user')->findOrFail($data['hr_staff_id']);

        $previousState = $absence->situacao;
        $absence->fill($data);
        $this->checkOverlaps($absence, $staff);
        if ($previousState !== $data['situacao']) {
            $this->applyDecision($absence, $data['situacao']);
        }
        $absence->save();
        $this->storeDocument($request, $absence);

        flash('Falta atualizada.')->success();

        return redirect()->route('backoffice.hr.vacations.index', ['ano' => $absence->data_inicio->year]);
    }

    /** Ação rápida na lista: justificar ou injustificar. */
    public function decide(Request $request, $id)
    {
        $absence = HrAbsence::findOrFail($id);
        $situacao = $request->validate(['situacao' => ['required', Rule::in(['justificada', 'injustificada'])]])['situacao'];

        if ($situacao === 'justificada' && $absence->motivo === 'sem_motivo') {
            flash('Para justificar, edite a falta e escolha o motivo.')->warning();
            return back();
        }

        $this->applyDecision($absence, $situacao);
        $absence->save();

        flash('Falta marcada como ' . mb_strtolower($absence->situacao_label) . '.')->success();
        return back();
    }

    public function destroy($id)
    {
        $absence = HrAbsence::findOrFail($id);
        if ($absence->documento_path) {
            Storage::disk(self::DISK)->delete($absence->documento_path);
        }
        $absence->delete();

        flash('Falta apagada.')->success();
        return back();
    }

    public function document($id)
    {
        $absence = HrAbsence::findOrFail($id);
        $own = HrAccess::ownStaffIdFilter(auth()->user());
        abort_unless($own === null || $absence->hr_staff_id === $own, 403);
        abort_unless($absence->documento_path && Storage::disk(self::DISK)->exists($absence->documento_path), 404);

        return Storage::disk(self::DISK)->response($absence->documento_path, $absence->documento_nome);
    }

    public function deleteDocument($id)
    {
        $absence = HrAbsence::findOrFail($id);
        if ($absence->documento_path) {
            Storage::disk(self::DISK)->delete($absence->documento_path);
            $absence->update(['documento_path' => null, 'documento_nome' => null]);
        }

        flash('Comprovativo removido.')->success();
        return back();
    }

    /** Faltas para o calendário da dashboard. */
    public function events(Request $request)
    {
        $from = Carbon::parse($request->input('start', now()->startOfMonth()))->startOfDay();
        $to = Carbon::parse($request->input('end', now()->endOfMonth()))->startOfDay();

        $own = HrAccess::ownStaffIdFilter(auth()->user());
        $canEdit = $own === null;

        $events = HrAbsence::with('staff.user')
            ->overlapping($from->toDateString(), $to->toDateString())
            ->when(!$canEdit, fn ($q) => $q->where('hr_staff_id', $own))
            ->get()
            ->map(function (HrAbsence $a) use ($canEdit) {
                $name = $a->staff->user->name ?? '—';
                $event = [
                    'title' => 'Falta – ' . $name,
                    'color' => $a->situacao_color,
                    'url' => $canEdit ? route('backoffice.hr.absences.edit', $a->id) : null,
                    'ausencia' => true,
                    'description' => $a->motivo_label . ' · ' . $a->situacao_label . ' · ' . $a->duracao
                        . ($a->situacao === 'justificada' && !$a->documento_path ? ' · sem comprovativo' : ''),
                ];

                if ($a->isPartial()) {
                    return $event + [
                        'start' => $a->data_inicio->toDateString() . 'T' . $a->hora_inicio,
                        'end' => $a->data_inicio->toDateString() . 'T' . $a->hora_fim,
                    ];
                }

                return $event + [
                    'start' => $a->data_inicio->toDateString(),
                    'end' => $a->data_fim->copy()->addDay()->toDateString(),
                    'allDay' => true,
                ];
            });

        return response()->json($events->values());
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'hr_staff_id' => ['required', 'exists:hr_staff,id'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
            'hora_inicio' => ['nullable', 'date_format:H:i', 'required_with:hora_fim'],
            'hora_fim' => ['nullable', 'date_format:H:i', 'required_with:hora_inicio', 'after:hora_inicio'],
            'motivo' => ['required', Rule::in(array_keys(config('hr.absence_reasons')))],
            'situacao' => ['required', Rule::in(array_keys(HrAbsence::STATES))],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            'documento' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,heic,doc,docx', 'max:10240'],
        ], [
            'data_fim.after_or_equal' => 'A data de fim tem de ser igual ou posterior à data de início.',
            'hora_fim.after' => 'A hora de fim tem de ser depois da hora de início.',
            'documento.max' => 'O comprovativo não pode ter mais de 10 MB.',
        ]);

        if ($data['situacao'] === 'justificada' && $data['motivo'] === 'sem_motivo') {
            throw ValidationException::withMessages(['motivo' => 'Para marcar como justificada, escolha o motivo da falta.']);
        }

        $from = Carbon::parse($data['data_inicio']);
        $to = Carbon::parse($data['data_fim']);

        // Falta de parte do dia: só num dia, conta horas em vez de dias
        if (!empty($data['hora_inicio'])) {
            if (!$from->isSameDay($to)) {
                throw ValidationException::withMessages(['hora_inicio' => 'Uma falta de algumas horas tem de ser num único dia.']);
            }
            $data['dias_uteis'] = 0;
            $data['horas'] = round(Carbon::parse($data['hora_inicio'])->diffInMinutes(Carbon::parse($data['hora_fim'])) / 60, 2);
        } else {
            $data['hora_inicio'] = $data['hora_fim'] = $data['horas'] = null;
            $data['dias_uteis'] = $this->workingDays->count($from, $to);
            if ($data['dias_uteis'] === 0) {
                throw ValidationException::withMessages(['data_inicio' => 'O período escolhido não tem dias úteis (só fins de semana ou feriados).']);
            }
        }

        unset($data['documento']);

        return $data;
    }

    private function checkOverlaps(HrAbsence $absence, HrStaff $staff): void
    {
        $from = $absence->data_inicio->toDateString();
        $to = $absence->data_fim->toDateString();

        $other = HrAbsence::where('hr_staff_id', $staff->id)
            ->when($absence->exists, fn ($q) => $q->where('id', '!=', $absence->id))
            ->overlapping($from, $to)
            ->first();
        if ($other) {
            throw ValidationException::withMessages([
                'data_inicio' => $staff->user->name . ' já tem uma falta registada de ' . $other->data_inicio->format('d/m') . ' a ' . $other->data_fim->format('d/m') . '.',
            ]);
        }

        $vacation = HrVacation::active()->where('hr_staff_id', $staff->id)->overlapping($from, $to)->first();
        if ($vacation) {
            throw ValidationException::withMessages([
                'data_inicio' => $staff->user->name . ' tem férias marcadas de ' . $vacation->data_inicio->format('d/m') . ' a ' . $vacation->data_fim->format('d/m') . ' nesse período.',
            ]);
        }
    }

    private function applyDecision(HrAbsence $absence, string $situacao): void
    {
        $absence->situacao = $situacao;
        $decided = $situacao !== 'aguarda';
        $absence->decided_by = $decided ? auth()->id() : null;
        $absence->decided_at = $decided ? now() : null;
    }

    private function storeDocument(Request $request, HrAbsence $absence): void
    {
        if (!$request->hasFile('documento')) {
            return;
        }

        if ($absence->documento_path) {
            Storage::disk(self::DISK)->delete($absence->documento_path);
        }

        $file = $request->file('documento');
        $absence->update([
            'documento_path' => $file->store('hr_absences', self::DISK),
            'documento_nome' => $file->getClientOriginalName(),
        ]);
    }
}
