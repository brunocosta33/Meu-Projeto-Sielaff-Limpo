<?php

namespace App\Http\Controllers;

use App\Models\HrShiftOverride;
use App\Models\HrStaff;
use App\Models\User;
use App\Services\HotlineRotation;
use App\Services\HrAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HrController extends Controller
{
    public function __construct(private HotlineRotation $rotation)
    {
    }

    // ---------- Colaboradores ----------

    public function staffIndex()
    {
        $this->authorizeStaff();
        $order = array_flip(array_keys(config('hr.functions')));
        $staff = HrStaff::with('user')->get()
            ->sortBy(fn ($s) => sprintf('%02d %s', $order[$s->funcao] ?? 99, $s->user->name ?? ''));
        return view('backoffice.hr.staff.index', compact('staff'));
    }

    public function staffCreate()
    {
        $this->authorizeStaff();
        $users = User::whereNull('deleted_at')
            ->whereNotIn('id', HrStaff::pluck('user_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('backoffice.hr.staff.form', ['member' => new HrStaff(['ativo' => true]), 'users' => $users]);
    }

    public function staffStore(Request $request)
    {
        $this->authorizeStaff();
        HrStaff::create($this->validateStaff($request));
        flash(__('Colaborador adicionado com sucesso!'))->success();
        return redirect()->route('backoffice.hr.staff.index');
    }

    public function staffEdit($id)
    {
        $this->authorizeStaff();
        $member = HrStaff::with('user')->findOrFail($id);
        return view('backoffice.hr.staff.form', ['member' => $member, 'users' => collect([$member->user])]);
    }

    public function staffUpdate(Request $request, $id)
    {
        $this->authorizeStaff();
        $member = HrStaff::findOrFail($id);
        $member->update($this->validateStaff($request, $member));
        flash(__('Colaborador atualizado com sucesso!'))->success();
        return redirect()->route('backoffice.hr.staff.index');
    }

    public function staffDelete($id)
    {
        $this->authorizeStaff();
        HrStaff::findOrFail($id)->delete();
        flash(__('Colaborador removido com sucesso!'))->success();
        return redirect()->route('backoffice.hr.staff.index');
    }

    private function authorizeStaff(): void
    {
        abort_unless(HrStaff::canManageStaff(auth()->user()), 403, __('Só o Coordenador e o Country Manager podem ver os Colaboradores.'));
    }

    private function validateStaff(Request $request, ?HrStaff $member = null): array
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id', Rule::unique('hr_staff', 'user_id')->ignore($member?->id)],
            'funcao' => ['required', Rule::in(array_keys(config('hr.functions')))],
            'rotation_group' => ['nullable', 'required_if:funcao,hotliner', Rule::in(['A', 'B'])],
            'zona' => ['nullable', Rule::in(array_keys(config('hr.zones')))],
        ], [
            'funcao.required' => __('Escolha a função do colaborador.'),
            'rotation_group.required_if' => __('Escolha o grupo de rotação do hotliner.'),
        ]);

        // Técnico no piquete: precisa de grupo (sábado A/B) e zona (Norte/Sul)
        if ($data['funcao'] === 'tecnico' && !empty($data['rotation_group']) && empty($data['zona'])) {
            throw ValidationException::withMessages(['zona' => __('Escolha a zona (Norte ou Sul) do técnico de piquete.')]);
        }

        $data['ativo'] = $request->boolean('ativo');
        if (!in_array($data['funcao'], ['hotliner', 'tecnico'], true)) {
            $data['rotation_group'] = null;
        }
        if ($data['funcao'] !== 'tecnico' || empty($data['rotation_group'])) {
            $data['zona'] = null;
        }

        return $data;
    }

    // ---------- Escalas (Hotline e Piquete) ----------

    public function hotlineSchedule(Request $request)
    {
        return $this->schedulePage($request, 'hotline');
    }

    public function piqueteSchedule(Request $request)
    {
        return $this->schedulePage($request, 'piquete');
    }

    private function schedulePage(Request $request, string $kind)
    {
        // Mês inicial do calendário (para voltar ao mês certo depois de gravar uma troca)
        $month = $request->filled('mes')
            ? Carbon::createFromFormat('Y-m', $request->mes)->startOfMonth()
            : now()->startOfMonth();

        $hotliners = HotlineRotation::staffFor($kind);
        $colors = HotlineRotation::colorMap($hotliners);
        $canEdit = HrAccess::isManager(auth()->user());

        // Quem não é gestão de RH só vê a sua parte
        if (!$canEdit) {
            $hotliners = $hotliners->where('user_id', auth()->id())->values();
        }
        $legend = $hotliners->map(fn ($s) => [
            'name' => $s->user->name,
            'group' => $s->rotation_group,
            'zona' => $s->zona ? __(config('hr.zones.' . $s->zona)) : null,
            'color' => $colors[$s->id],
        ]);

        $upcomingOverrides = HrShiftOverride::with(['staff.user', 'substitute'])
            ->whereIn('hr_staff_id', HotlineRotation::staffFor($kind)->pluck('id'))
            ->when(!$canEdit, fn ($q) => $q->where(fn ($w) => $w
                ->whereIn('hr_staff_id', $hotliners->pluck('id'))
                ->orWhere('substitute_user_id', auth()->id())))
            ->whereDate('data', '>=', now()->startOfMonth())
            ->orderBy('data')
            ->limit(50)
            ->get();

        return view('backoffice.hr.hotline.index', compact('month', 'hotliners', 'legend', 'upcomingOverrides', 'kind', 'canEdit'));
    }

    /** Horário da Equipa: o mesmo calendário da dashboard, só de consulta (para quem não usa a dashboard, como os técnicos). */
    public function teamCalendar()
    {
        return view('backoffice.hr.team_calendar');
    }

    /** Página da escala a que o colaborador pertence, no mês indicado. */
    private function scheduleRedirect(HrStaff $staff, Carbon $date)
    {
        $route = HotlineRotation::kindOf($staff) === 'piquete' ? 'backoffice.hr.piquete.index' : 'backoffice.hr.hotline.index';

        return redirect()->route($route, ['mes' => $date->format('Y-m')]);
    }

    /** Outros colaboradores da mesma escala (para trocas). */
    private function sameScheduleStaff(HrStaff $staff)
    {
        return HotlineRotation::staffFor(HotlineRotation::kindOf($staff))->where('id', '!=', $staff->id)->values();
    }

    /**
     * Eventos para o calendário da dashboard (FullCalendar envia start/end do intervalo visível).
     * Título no formato "Função – Nome", para juntar mais tarde os técnicos de serviço.
     */
    public function calendarEvents(Request $request)
    {
        $from = Carbon::parse($request->input('start', now()->startOfMonth()))->startOfDay();
        $to = Carbon::parse($request->input('end', now()->endOfMonth()))->startOfDay()->subDay();

        if ($to->lt($from) || $from->diffInDays($to) > 62) {
            return response()->json([]);
        }

        $kind = in_array($request->input('tipo'), ['hotline', 'piquete'], true) ? $request->input('tipo') : 'todos';

        $canEdit = HrAccess::isManager(auth()->user());
        $shifts = $this->rotation->shifts($from, $to, HotlineRotation::staffFor($kind));

        // Calendário da equipa (dashboard e Horário da Equipa, tipo "todos"): todos veem a escala completa,
        // com os motivos das ausências escondidos para quem não é gestão de RH
        $fullDashboardView = $kind === 'todos';

        // Quem não é gestão de RH só vê os seus turnos (incluindo os que faz em substituição)
        if (!$canEdit && !$fullDashboardView) {
            $me = auth()->id();
            $shifts = $shifts->filter(fn ($s) => $s->staff->user_id === $me || $s->worker_id === $me);
        }

        // O motivo real das ausências só é visível para a gestão de RH
        $noteOf = fn ($s) => $canEdit ? $s->override_note : $s->public_note;

        $events = $shifts->map(fn ($s) => [
            'title' => $s->funcao . ' – ' . $s->name . ($s->zona ? ' · ' . $s->zona : ''),
            'start' => $s->start->format('Y-m-d\TH:i:s'),
            'end' => $s->end->format('Y-m-d\TH:i:s'),
            'color' => $s->color,
            'classNames' => $s->override ? ['fc-duty-override'] : [],
            'url' => $canEdit ? route('backoffice.hr.hotline.override.create', ['staff' => $s->staff->id, 'data' => $s->date->toDateString()]) : null,
            'funcao' => $s->funcao,
            'escala' => $s->kind,
            'semSubstituto' => $s->worker_id === null,
            'nome' => $s->name . ($s->zona ? ' · ' . $s->zona : ''),
            'horario' => $s->hours,
            'substituicao' => $noteOf($s),
            // Só quando quem trabalha não é quem estava escalado
            'titular' => $s->worker_id === $s->staff->user_id ? null : $s->titular,
            'motivo' => $canEdit ? $s->motivo : $s->public_motivo,
            'troca' => $s->troca,
            'description' => __('Horário') . ': ' . $s->hours . ($noteOf($s) ? ' · ' . $noteOf($s) : ''),
        ])->values();

        return response()->json($events);
    }

    public function overrideCreate(Request $request)
    {
        $request->validate([
            'staff' => ['required', 'exists:hr_staff,id'],
            'data' => ['required', 'date'],
        ], [
            'staff.required' => __('Escolha de quem é o turno a trocar.'),
            'data.required' => __('Escolha o dia.'),
        ]);

        $staff = HrStaff::with('user')->findOrFail($request->staff);
        $date = Carbon::parse($request->data);
        $shift = $this->rotation->scheduledShift($staff, $date);

        if (!$shift) {
            flash(__(':nome não tem turno a :data, não há nada para trocar nesse dia.', ['nome' => $staff->user->name, 'data' => $date->format('d/m/Y')]))->warning();
            return $this->scheduleRedirect($staff, $date);
        }

        $override = HrShiftOverride::where('hr_staff_id', $staff->id)->whereDate('data', $date)->first();
        $otherHotliners = $this->sameScheduleStaff($staff)->pluck('user_id');

        return view('backoffice.hr.hotline.override', [
            'staff' => $staff,
            'date' => $date,
            'shift' => $shift,
            'override' => $override,
            'users' => $this->substituteOptions($staff),
            'otherHotlinerUserIds' => $otherHotliners,
        ]);
    }

    public function overrideStore(Request $request)
    {
        $data = $request->validate([
            'hr_staff_id' => ['required', 'exists:hr_staff,id'],
            'data' => ['required', 'date'],
            'substitute_user_id' => ['nullable', 'exists:users,id'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fim' => ['nullable', 'date_format:H:i'],
            'motivo' => ['required', Rule::in(array_keys(config('hr.override_reasons')))],
            'observacoes' => ['nullable', 'string'],
            'devolucao_data' => ['nullable', 'date', 'different:data'],
        ], [
            'devolucao_data.different' => __('O dia de devolução tem de ser diferente do dia trocado.'),
        ]);

        $staff = HrStaff::with('user')->findOrFail($data['hr_staff_id']);
        $date = Carbon::parse($data['data']);
        $returnDate = !empty($data['devolucao_data']) && $data['motivo'] === 'troca' ? Carbon::parse($data['devolucao_data']) : null;
        unset($data['devolucao_data']);

        // Troca de dois dias: o colega da mesma escala também cede um turno dele
        $returnStaff = null;
        if ($returnDate) {
            $returnStaff = $this->sameScheduleStaff($staff)->firstWhere('user_id', (int) ($data['substitute_user_id'] ?? 0));
            if (!$returnStaff) {
                throw ValidationException::withMessages(['devolucao_data' => __('A devolução só é possível quando a troca é feita com um colega da mesma escala.')]);
            }
            if (!$this->rotation->scheduledShift($returnStaff, $returnDate)) {
                throw ValidationException::withMessages([
                    'devolucao_data' => __(':nome está de folga a :data, escolha um dia em que tenha turno.', ['nome' => $returnStaff->user->name, 'data' => $returnDate->format('d/m/Y')]),
                ]);
            }
        }

        HrShiftOverride::updateOrCreate(
            ['hr_staff_id' => $staff->id, 'data' => $date->toDateString()],
            $data + ['created_by' => auth()->id()]
        );

        if ($returnStaff) {
            HrShiftOverride::updateOrCreate(
                ['hr_staff_id' => $returnStaff->id, 'data' => $returnDate->toDateString()],
                [
                    'substitute_user_id' => $staff->user_id,
                    'hora_inicio' => null,
                    'hora_fim' => null,
                    'motivo' => 'troca',
                    'observacoes' => 'Troca com ' . $returnStaff->user->name . ' pelo dia ' . $date->format('d/m/Y'),
                    'created_by' => auth()->id(),
                ]
            );
            flash(__('Troca gravada: :nome faz o dia :data e :outro faz o dia :devolucao.', [
                'nome' => $returnStaff->user->name, 'data' => $date->format('d/m'),
                'outro' => $staff->user->name, 'devolucao' => $returnDate->format('d/m'),
            ]))->success();
        } else {
            $this->warnIfSubstituteBusy($data['substitute_user_id'] ?? null, $date, $staff);
            flash(__('Substituição gravada com sucesso!'))->success();
        }

        return $this->scheduleRedirect($staff, $date);
    }

    public function overrideDelete($id)
    {
        $override = HrShiftOverride::with('staff')->findOrFail($id);
        $override->delete();

        flash(__('Substituição removida. O dia volta à rotação normal.'))->success();
        return $this->scheduleRedirect($override->staff, $override->data);
    }

    private function substituteOptions(HrStaff $staff)
    {
        $hotlinerUserIds = $this->sameScheduleStaff($staff)->pluck('user_id');

        // Colegas da mesma escala primeiro, depois os restantes utilizadores
        return User::whereNull('deleted_at')
            ->where('id', '!=', $staff->user_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->sortByDesc(fn ($u) => $hotlinerUserIds->contains($u->id))
            ->values();
    }

    private function warnIfSubstituteBusy(?int $userId, Carbon $date, HrStaff $replaced): void
    {
        if (!$userId) {
            return;
        }

        $other = HotlineRotation::staffFor()->firstWhere('user_id', $userId);
        if (!$other || $other->id === $replaced->id) {
            return;
        }

        $ownShift = $this->rotation->scheduledShift($other, $date);
        $ownOverride = HrShiftOverride::where('hr_staff_id', $other->id)->whereDate('data', $date)->exists();

        if ($ownShift && !$ownOverride) {
            flash(__('Atenção: o substituto já tem turno próprio nesse dia (:horas).', ['horas' => HotlineRotation::formatHours($ownShift)]))->warning();
        }
    }
}
