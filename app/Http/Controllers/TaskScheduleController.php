<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskScheduleController extends Controller
{
    public function index(Request $request)
    {
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
    // Buscar IDs de tasks já agendadas (em qualquer agendamento)
    $agendadasTaskIds = DB::table('task_schedules')->pluck('task_id');

    $tasks = Task::whereNotIn('id', $agendadasTaskIds)->get();
    $users = User::all();

    return view('backoffice.task_schedules.create', compact('tasks', 'users'));
    }

  public function store(Request $request)
    {
        // validação (mantém como está)

        $isRepetir = (int) $request->input('repetir', 0);
        $dataLimite = $isRepetir ? null : $request->data_limite;
        $horaLimite = $isRepetir ? null : $request->hora_limite;

        try {
                $schedule = TaskSchedule::create([
                    'task_id'     => $request->task_id,
                    'prioridade'  => $request->prioridade,
                    'data_limite' => $dataLimite,
                    'hora_limite' => $horaLimite,
                    'activa'      => $request->has('activa') ? 1 : 0,
                    'grupo'       => $request->has('grupo') ? 1 : 0,
                    'repetir'     => $isRepetir,
                    'estado'      => 'Pendente',
                    'user_id'     => $request->user_ids[0],
                    'description' => $request->description,
                ]);

            $schedule->users()->sync($request->user_ids);

            return redirect()->route('backoffice.task_schedules.index')->with('success', 'Agendamento criado com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao gravar agendamento: ' . $e->getMessage());
        }
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'prioridade' => 'required|in:Alta,Média,Baixa',
            'data_limite' => 'required|date',
            'hora_limite' => 'required',
            'activa' => 'nullable|boolean',
            'grupo' => 'nullable|boolean',
            'repetir' => 'nullable|boolean',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $schedule = TaskSchedule::findOrFail($id);
        $schedule->update([
            'task_id' => $request->task_id,
            'prioridade' => $request->prioridade,
            'data_limite' => $request->data_limite,
            'hora_limite' => $request->hora_limite,
            'activa' => $request->input('activa', 0),
            'grupo' => $request->has('grupo'),
            'repetir' => $request->repetir ?? false,
        ]);

        $schedule->users()->sync($request->user_ids);

        return redirect()->route('backoffice.task_schedules.index')->with('success', 'Agendamento atualizado com sucesso.');
    }

    public function edit($id)
    {
        $schedule = TaskSchedule::with('users')->findOrFail($id);
        $tasks = Task::all();
        $users = User::all();

        return view('backoffice.task_schedules.edit', compact('schedule', 'tasks', 'users'));
    }


    public function destroy($id)
    {
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
        $taskSchedules = $user->taskSchedules()->with('task')->get();

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

        $estado = $request->input('estado');
        $comentarios = $request->input('comentarios');

        $schedule->users()->updateExistingPivot($user->id, [
            'estado' => $estado,
            'comentarios' => $comentarios,
            'data_conclusao' => $estado === 'Concluída' ? now() : null,
        ]);

        return redirect()->route('backoffice.task_schedules.minhas')->with('success', 'Tarefa atualizada com sucesso.');
    }



}
