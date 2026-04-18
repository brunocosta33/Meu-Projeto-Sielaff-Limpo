<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskScheduleController extends Controller
{
    // Atualiza uma ocorrência individual (concluir apenas uma ocorrência)
    public function updateOcorrencia(Request $request, $id)
    {
        $user = auth()->user();
        $ocorrencia = \App\Models\TaskScheduleOccurrence::findOrFail($id);
        // Garante que só o próprio usuário pode concluir sua ocorrência
        if ($ocorrencia->user_id !== $user->id) {
            abort(403, 'Não autorizado.');
        }
        $ocorrencia->estado = $request->input('estado', 'Concluída');
        $ocorrencia->data_conclusao = $ocorrencia->estado === 'Concluída' ? now() : null;
        $ocorrencia->save();
        return redirect()->route('backoffice.task_schedules.minhas')->with('success', 'Ocorrência marcada como concluída.');
    }
    public function index(Request $request)
    {
        abort_if($this->isTechnician(), 403);

        $q = TaskSchedule::with([
            'task',
            'users' => fn($uq) => $uq->select('users.id', 'users.name'),
        ])->orderBy('created_at', 'desc');

        // Filtro por prioridade (opcional)
        if ($request->filled('prioridade')) {
            $q->where('prioridade', $request->prioridade);
        }

        // Filtro "Por concluir" = NÃO totalmente concluído
        // (1) não tem utilizadores atribuídos OU
        // (2) tem pelo menos um utilizador com pivot.estado != 'Concluída' (ou NULL)
        if ($request->estado === 'por_concluir') {
            $q->where(function ($w) {
                $w->whereDoesntHave('users')
                ->orWhereHas('users', function ($sub) {
                    $sub->where(function ($s) {
                        $s->whereNull('task_schedule_user.estado')
                            ->orWhere('task_schedule_user.estado', '!=', 'Concluída');
                    });
                });
            });
        }

        // Pesquisa pelo título da tarefa (opcional)
        if ($request->filled('pesquisa')) {
            $q->whereHas('task', function ($t) use ($request) {
                $t->where('title', 'like', '%' . $request->pesquisa . '%');
            });
        }

        $schedules = $q->get();

        return view('backoffice.task_schedules.index', compact('schedules'));
    }

    public function create()
    {
        abort_if($this->isTechnician(), 403);

        $tasks = Task::whereDoesntHave('schedules')
            ->orderBy('title')
            ->get();
        $users = User::orderBy('name')->get();

        return view('backoffice.task_schedules.create', compact('tasks', 'users'));
    }

    public function store(Request $request)
    {
        abort_if($this->isTechnician(), 403);

        $request->validate([
            'task_id' => ['required', 'exists:tasks,id'],
            'prioridade' => ['required', Rule::in(['Alta', 'Média', 'Baixa'])],
            'activa' => ['nullable', 'boolean'],
            'grupo' => ['nullable', 'boolean'],
            'repetir' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:255'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id'],
            'data_limite' => ['required_if:repetir,0', 'nullable', 'date', 'after_or_equal:today'],
            'hora_limite' => ['required_if:repetir,0', 'nullable'],
            'initial_date' => ['required_if:repetir,1', 'nullable', 'date', 'after_or_equal:today'],
            'final_date' => ['required_if:repetir,1', 'nullable', 'date', 'after_or_equal:initial_date'],
            'time' => ['required_if:repetir,1', 'nullable'],
            'period' => ['required_if:repetir,1', 'nullable', Rule::in(['day', 'week', 'month', 'year'])],
        ], [
            'task_id.required' => 'Selecione uma tarefa.',
            'task_id.exists' => 'A tarefa selecionada não é válida.',
            'prioridade.required' => 'Selecione a prioridade.',
            'prioridade.in' => 'A prioridade selecionada não é válida.',
            'user_ids.required' => 'Selecione pelo menos um colaborador.',
            'user_ids.array' => 'Selecione pelo menos um colaborador.',
            'user_ids.min' => 'Selecione pelo menos um colaborador.',
            'user_ids.*.exists' => 'Um dos colaboradores selecionados não é válido.',
            'data_limite.required_if' => 'Preencha a data limite.',
            'data_limite.date' => 'A data limite não é válida.',
            'data_limite.after_or_equal' => 'A data limite não pode ser anterior a hoje.',
            'hora_limite.required_if' => 'Preencha a hora limite.',
            'initial_date.required_if' => 'Preencha a data de início.',
            'initial_date.date' => 'A data de início não é válida.',
            'initial_date.after_or_equal' => 'A data de início não pode ser anterior a hoje.',
            'final_date.required_if' => 'Preencha a data de fim.',
            'final_date.date' => 'A data de fim não é válida.',
            'final_date.after_or_equal' => 'A data de fim não pode ser anterior à data de início.',
            'time.required_if' => 'Preencha a hora da repetição.',
            'period.required_if' => 'Selecione a repetição.',
            'period.in' => 'A repetição selecionada não é válida.',
            'description.max' => 'A descrição não pode ter mais de 255 caracteres.',
        ], [
            'task_id' => 'tarefa',
            'prioridade' => 'prioridade',
            'user_ids' => 'colaboradores',
            'data_limite' => 'data limite',
            'hora_limite' => 'hora limite',
            'initial_date' => 'data de início',
            'final_date' => 'data de fim',
            'time' => 'hora da repetição',
            'period' => 'repetição',
            'description' => 'descrição',
        ]);

        $isRepetir = (int) $request->input('repetir', 0);
        $dataLimite = $isRepetir ? null : $request->data_limite;
        $horaLimite = $isRepetir ? null : $request->hora_limite;

        try {
            $data = [
                'task_id'     => $request->task_id,
                'prioridade'  => $request->prioridade,
                'data_limite' => $dataLimite,
                'hora_limite' => $horaLimite,
                'activa'      => $request->has('activa') ? 1 : 0,
                'grupo'       => $request->has('grupo') ? 1 : 0,
                'repetir'     => $isRepetir,
                'estado'      => 'Pendente',
                'user_id'     => $request->user_ids[0] ?? null,
                'description' => $request->description,
            ];
            if ($isRepetir) {
                $data['initial_date'] = $request->initial_date;
                $data['final_date'] = $request->final_date;
                $data['time'] = $request->time;
                $data['period'] = $request->period;
                if ($request->has('days_of_week')) {
                    $data['days_of_week'] = is_array($request->days_of_week) ? json_encode($request->days_of_week) : $request->days_of_week;
                }
            }
            $schedule = TaskSchedule::create($data);
            $schedule->users()->sync($request->user_ids);

            return redirect()->route('backoffice.task_schedules.index')->with('success', 'Agendamento criado com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao gravar agendamento: ' . $e->getMessage());
        }
    }


    public function update(Request $request, $id)
    {
        abort_if($this->isTechnician(), 403);

        $editMode = $request->input('edit_mode', 'apenas_esta');
        $schedule = TaskSchedule::findOrFail($id);
        $isRepetir = (bool) $request->boolean('repetir', $schedule->repetir);

        $request->validate([
            'task_id' => ['required', 'exists:tasks,id'],
            'prioridade' => ['required', Rule::in(['Alta', 'Média', 'Baixa'])],
            'activa' => ['nullable', 'boolean'],
            'grupo' => ['nullable', 'boolean'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id'],
            'data_limite' => [$isRepetir ? 'nullable' : 'required', 'date', 'after_or_equal:today'],
            'hora_limite' => [$isRepetir ? 'nullable' : 'required'],
            'initial_date' => [$isRepetir ? 'required' : 'nullable', 'date', 'after_or_equal:today'],
            'final_date' => [$isRepetir ? 'required' : 'nullable', 'date', 'after_or_equal:initial_date'],
            'time' => [$isRepetir ? 'required' : 'nullable'],
            'period' => [$isRepetir ? 'required' : 'nullable', Rule::in(['day', 'week', 'month', 'year'])],
        ], [
            'task_id.required' => 'Selecione uma tarefa.',
            'task_id.exists' => 'A tarefa selecionada não é válida.',
            'prioridade.required' => 'Selecione a prioridade.',
            'prioridade.in' => 'A prioridade selecionada não é válida.',
            'user_ids.required' => 'Selecione pelo menos um colaborador.',
            'user_ids.array' => 'Selecione pelo menos um colaborador.',
            'user_ids.min' => 'Selecione pelo menos um colaborador.',
            'user_ids.*.exists' => 'Um dos colaboradores selecionados não é válido.',
            'data_limite.required' => 'Preencha a data limite.',
            'data_limite.date' => 'A data limite não é válida.',
            'data_limite.after_or_equal' => 'A data limite não pode ser anterior a hoje.',
            'hora_limite.required' => 'Preencha a hora limite.',
            'initial_date.required' => 'Preencha a data de início.',
            'initial_date.date' => 'A data de início não é válida.',
            'initial_date.after_or_equal' => 'A data de início não pode ser anterior a hoje.',
            'final_date.required' => 'Preencha a data de fim.',
            'final_date.date' => 'A data de fim não é válida.',
            'final_date.after_or_equal' => 'A data de fim não pode ser anterior à data de início.',
            'time.required' => 'Preencha a hora da repetição.',
            'period.required' => 'Selecione a repetição.',
            'period.in' => 'A repetição selecionada não é válida.',
        ]);

        $payload = [
            'task_id' => $request->task_id,
            'prioridade' => $request->prioridade,
            'data_limite' => $isRepetir ? null : $request->data_limite,
            'hora_limite' => $isRepetir ? null : $request->hora_limite,
            'initial_date' => $isRepetir ? $request->initial_date : null,
            'final_date' => $isRepetir ? $request->final_date : null,
            'time' => $isRepetir ? $request->time : null,
            'period' => $isRepetir ? $request->period : null,
            'activa' => $request->boolean('activa'),
            'grupo' => $request->boolean('grupo'),
            'repetir' => $isRepetir,
        ];

        if ($editMode === 'todas_futuras' && $schedule->repetir) {
            // Atualiza todas as ocorrências futuras da mesma série (mesmo task_id, mesmo padrão de recorrência)
            $futuras = TaskSchedule::where('task_id', $schedule->task_id)
                ->where('repetir', 1)
                ->where('id', '>=', $schedule->id)
                ->get();
            foreach ($futuras as $item) {
                $item->update($payload);
                $item->users()->sync($request->user_ids);
            }
        } else {
            // Edita só a ocorrência atual
            $schedule->update($payload);
            $schedule->users()->sync($request->user_ids);
        }

        return redirect()->route('backoffice.task_schedules.index')->with('success', 'Agendamento atualizado com sucesso.');
    }

    public function edit($id)
    {
        abort_if($this->isTechnician(), 403);

        $schedule = TaskSchedule::with([
            'task:id,title',
            'users:id,name',
        ])->findOrFail($id);
        $users = User::select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('backoffice.task_schedules.edit', compact('schedule', 'users'));
    }


    public function destroy($id)
    {
        abort_if($this->isTechnician(), 403);

        $schedule = TaskSchedule::findOrFail($id);
        $taskId = $schedule->task_id;
        // Verifica se existem outros agendamentos para esta tarefa (exceto o atual)
        $agendamentos = TaskSchedule::where('task_id', $taskId)->where('id', '!=', $id)->count();
        $schedule->users()->detach();
        $schedule->delete();
        // Se não há mais agendamentos para esta tarefa, apaga a tarefa também
        if ($agendamentos == 0) {
            $task = \App\Models\Task::find($taskId);
            if ($task) {
                $task->delete();
            }
        }
        return redirect()->route('backoffice.task_schedules.index')->with('success', 'Agendamento removido com sucesso.');
    }

    public function minhasTarefas()
    {
        $user = auth()->user();
        $taskSchedules = $user->taskSchedules()
            ->with('task')
            ->orderByRaw('COALESCE(data_limite, initial_date) asc')
            ->orderByRaw('COALESCE(hora_limite, time) asc')
            ->get();
        return view('backoffice.task_schedules.minhas', compact('taskSchedules'));
    }


    public function getTaskDescription(Request $request)
    {
        $task = Task::find($request->task_id);

        if (!$task) {
            return response('', 404); 
        }

        return response($task->description, 200)
            ->header('Content-Type', 'text/plain');
    }
    public function show($id)
    {
        abort_if($this->isTechnician(), 403);

        $schedule = TaskSchedule::with(['task', 'users'])->findOrFail($id);

        return view('backoffice.task_schedules.show', compact('schedule'));
    }

    public function showMinhas($id)
    {
        $user = auth()->user();
        $schedule = TaskSchedule::with('task', 'users')->findOrFail($id);

        $pivot = $schedule->users->firstWhere('id', $user->id)?->pivot;

        if (!$pivot) {
            abort(403, 'Não tem acesso a esta tarefa.');
        }

        return view('backoffice.task_schedules.show_minhas', compact('schedule', 'pivot'));
    }

    public function updateMinhas(Request $request, $id)
    {
        $user = auth()->user();
        $schedule = TaskSchedule::findOrFail($id);

        if (!$schedule->users->contains($user->id)) {
            abort(403, 'Não autorizado.');
        }

        $request->validate([
            'estado' => ['required', Rule::in(['Em Execução', 'Concluída'])],
            'comentario' => ['nullable', 'string', 'max:255'],
        ]);

        $estado = $request->input('estado');
        $comentarios = $request->input('comentario', $request->input('comentarios'));
        $concluirMode = $request->input('concluir_mode', 'apenas_esta');

        if ($schedule->repetir && $estado === 'Concluída' && $concluirMode === 'todas_futuras') {
            // Marca todas as ocorrências futuras da mesma série (mesmo task_id, mesmo padrão de recorrência)
            $futuras = TaskSchedule::where('task_id', $schedule->task_id)
                ->where('repetir', 1)
                ->where('id', '>=', $schedule->id)
                ->get();
            foreach ($futuras as $item) {
                $item->users()->updateExistingPivot($user->id, [
                    'estado' => 'Concluída',
                    'comentarios' => $comentarios,
                    'data_conclusao' => now(),
                ]);
            }
        } else {
            // Só esta ocorrência
            $schedule->users()->updateExistingPivot($user->id, [
                'estado' => $estado,
                'comentarios' => $comentarios,
                'data_conclusao' => $estado === 'Concluída' ? now() : null,
            ]);
        }

        return redirect()->route('backoffice.task_schedules.minhas')->with('success', 'Tarefa atualizada com sucesso.');
    }

    // Marca todas as ocorrências futuras como concluídas para o usuário logado
    public function concluirTodasOcorrencias(Request $request, $id)
    {
        $user = auth()->user();
        $schedule = TaskSchedule::findOrFail($id);

        // Só permite se for tarefa recorrente
        if (!$schedule->repetir) {
            return redirect()->back()->with('error', 'A tarefa não é recorrente.');
        }

        // Marca o pivot do usuário como concluído
        $schedule->users()->updateExistingPivot($user->id, [
            'estado' => 'Concluída',
            'data_conclusao' => now(),
        ]);

        return redirect()->route('backoffice.task_schedules.minhas')->with('success', 'Todas as ocorrências futuras foram marcadas como concluídas.');
    }

    private function isTechnician(): bool
    {
        $user = auth()->user();

        return $user && $user->hasRole('user');
    }

}
