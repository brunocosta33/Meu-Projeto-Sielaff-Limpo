<?php

namespace App\Http\Controllers;

use App\Models\HrShiftOverride;
use App\Models\HrStaff;
use App\Models\User;
use App\Services\HotlineRotation;
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
        $order = array_flip(array_keys(config('hr.functions')));
        $staff = HrStaff::with('user')->get()
            ->sortBy(fn ($s) => sprintf('%02d %s', $order[$s->funcao] ?? 99, $s->user->name ?? ''));
        return view('backoffice.hr.staff.index', compact('staff'));
    }

    public function staffCreate()
    {
        $users = User::whereNull('deleted_at')
            ->whereNotIn('id', HrStaff::pluck('user_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('backoffice.hr.staff.form', ['member' => new HrStaff(['ativo' => true]), 'users' => $users]);
    }

    public function staffStore(Request $request)
    {
        HrStaff::create($this->validateStaff($request));
        flash('Colaborador adicionado com sucesso!')->success();
        return redirect()->route('backoffice.hr.staff.index');
    }

    public function staffEdit($id)
    {
        $member = HrStaff::with('user')->findOrFail($id);
        return view('backoffice.hr.staff.form', ['member' => $member, 'users' => collect([$member->user])]);
    }

    public function staffUpdate(Request $request, $id)
    {
        $member = HrStaff::findOrFail($id);
        $member->update($this->validateStaff($request, $member));
        flash('Colaborador atualizado com sucesso!')->success();
        return redirect()->route('backoffice.hr.staff.index');
    }

    public function staffDelete($id)
    {
        HrStaff::findOrFail($id)->delete();
        flash('Colaborador removido com sucesso!')->success();
        return redirect()->route('backoffice.hr.staff.index');
    }

    private function validateStaff(Request $request, ?HrStaff $member = null): array
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id', Rule::unique('hr_staff', 'user_id')->ignore($member?->id)],
            'funcao' => ['required', Rule::in(array_keys(config('hr.functions')))],
            'rotation_group' => ['nullable', 'required_if:funcao,hotliner', Rule::in(['A', 'B'])],
        ], [
            'funcao.required' => 'Escolha a função do colaborador.',
            'rotation_group.required_if' => 'Escolha o grupo de rotação do hotliner.',
        ]);

        $data['ativo'] = $request->boolean('ativo');
        if ($data['funcao'] !== 'hotliner') {
            $data['rotation_group'] = null;
        }

        return $data;
    }

    // ---------- Escala dos Hotliners ----------

    public function hotlineSchedule(Request $request)
    {
        // Mês inicial do calendário (para voltar ao mês certo depois de gravar uma troca)
        $month = $request->filled('mes')
            ? Carbon::createFromFormat('Y-m', $request->mes)->startOfMonth()
            : now()->startOfMonth();

        $hotliners = HrStaff::activeHotliners()->with('user')->orderBy('rotation_group')->get();
        $legend = $hotliners->values()->map(fn ($s, $i) => [
            'name' => $s->user->name,
            'group' => $s->rotation_group,
            'color' => HotlineRotation::COLORS[$i % count(HotlineRotation::COLORS)],
        ]);

        $upcomingOverrides = HrShiftOverride::with(['staff.user', 'substitute'])
            ->whereDate('data', '>=', now()->startOfMonth())
            ->orderBy('data')
            ->limit(50)
            ->get();

        return view('backoffice.hr.hotline.index', compact('month', 'hotliners', 'legend', 'upcomingOverrides'));
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

        $events = $this->rotation->shifts($from, $to)->map(fn ($s) => [
            'title' => $s->funcao . ' – ' . $s->name,
            'start' => $s->start->format('Y-m-d\TH:i:s'),
            'end' => $s->end->format('Y-m-d\TH:i:s'),
            'color' => $s->color,
            'classNames' => $s->override ? ['fc-duty-override'] : [],
            'url' => route('backoffice.hr.hotline.override.create', ['staff' => $s->staff->id, 'data' => $s->date->toDateString()]),
            'funcao' => $s->funcao,
            'nome' => $s->name,
            'horario' => $s->hours,
            'substituicao' => $s->override_note,
            'description' => __('Horário') . ': ' . $s->hours . ($s->override_note ? ' · ' . $s->override_note : ''),
        ])->values();

        return response()->json($events);
    }

    public function overrideCreate(Request $request)
    {
        $request->validate([
            'staff' => ['required', 'exists:hr_staff,id'],
            'data' => ['required', 'date'],
        ], [
            'staff.required' => 'Escolha o hotliner cujo turno vai ser trocado.',
            'data.required' => 'Escolha o dia.',
        ]);

        $staff = HrStaff::with('user')->findOrFail($request->staff);
        $date = Carbon::parse($request->data);
        $shift = $this->rotation->scheduledShift($staff, $date);

        if (!$shift) {
            flash($staff->user->name . ' está de folga a ' . $date->format('d/m/Y') . ', não há turno para trocar nesse dia.')->warning();
            return redirect()->route('backoffice.hr.hotline.index', ['mes' => $date->format('Y-m')]);
        }

        $override = HrShiftOverride::where('hr_staff_id', $staff->id)->whereDate('data', $date)->first();
        $otherHotliners = HrStaff::activeHotliners()->where('id', '!=', $staff->id)->pluck('user_id');

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
            'devolucao_data.different' => 'O dia de devolução tem de ser diferente do dia trocado.',
        ]);

        $staff = HrStaff::with('user')->findOrFail($data['hr_staff_id']);
        $date = Carbon::parse($data['data']);
        $returnDate = !empty($data['devolucao_data']) && $data['motivo'] === 'troca' ? Carbon::parse($data['devolucao_data']) : null;
        unset($data['devolucao_data']);

        // Troca de dois dias: o colega também cede um turno dele ao hotliner original
        $returnStaff = null;
        if ($returnDate) {
            $returnStaff = HrStaff::activeHotliners()->with('user')->where('user_id', $data['substitute_user_id'] ?? 0)->first();
            if (!$returnStaff) {
                throw ValidationException::withMessages(['devolucao_data' => 'A devolução só é possível quando a troca é feita com outro hotliner.']);
            }
            if (!$this->rotation->scheduledShift($returnStaff, $returnDate)) {
                throw ValidationException::withMessages([
                    'devolucao_data' => $returnStaff->user->name . ' está de folga a ' . $returnDate->format('d/m/Y') . ', escolha um dia em que tenha turno.',
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
            flash('Troca gravada: ' . $returnStaff->user->name . ' faz o dia ' . $date->format('d/m')
                . ' e ' . $staff->user->name . ' faz o dia ' . $returnDate->format('d/m') . '.')->success();
        } else {
            $this->warnIfSubstituteBusy($data['substitute_user_id'] ?? null, $date, $staff);
            flash('Substituição gravada com sucesso!')->success();
        }

        return redirect()->route('backoffice.hr.hotline.index', ['mes' => $date->format('Y-m')]);
    }

    public function overrideDelete($id)
    {
        $override = HrShiftOverride::findOrFail($id);
        $month = $override->data->format('Y-m');
        $override->delete();

        flash('Substituição removida. O dia volta à rotação normal.')->success();
        return redirect()->route('backoffice.hr.hotline.index', ['mes' => $month]);
    }

    private function substituteOptions(HrStaff $staff)
    {
        $hotlinerUserIds = HrStaff::activeHotliners()->pluck('user_id');

        // Outros hotliners primeiro, depois os restantes utilizadores
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

        $other = HrStaff::activeHotliners()->where('user_id', $userId)->first();
        if (!$other || $other->id === $replaced->id) {
            return;
        }

        $ownShift = $this->rotation->scheduledShift($other, $date);
        $ownOverride = HrShiftOverride::where('hr_staff_id', $other->id)->whereDate('data', $date)->exists();

        if ($ownShift && !$ownOverride) {
            flash('Atenção: o substituto já tem turno próprio nesse dia (' . HotlineRotation::formatHours($ownShift) . ').')->warning();
        }
    }
}
