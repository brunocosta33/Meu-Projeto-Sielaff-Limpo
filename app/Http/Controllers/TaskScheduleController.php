<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskSchedule;
use App\Models\User;
use Illuminate\Http\Request;

class TaskScheduleController extends Controller
{
    public function index()
    {
        $schedules = TaskSchedule::with('task', 'users')->orderBy('created_at', 'desc')->get();
        return view('backoffice.task_schedules.index', compact('schedules'));
    }

    public function create()
    {
        // Buscar IDs de tasks já concluídas em algum agendamento (usando a tabela pivot diretamente)
        $concluidasTaskIds = \DB::table('task_schedule_user')
            ->where('estado', 'Concluída')
            ->pluck('task_schedule_id');

        $tasks = Task::whereNotIn('id', function($query) use ($concluidasTaskIds) {
            $query->select('task_id')
                ->from('task_schedules')
                ->whereIn('id', $concluidasTaskIds);
        })->get();
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
                'activa'      => $request->has('activa'),
                'grupo'       => $request->has('grupo'),
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
            'activa' => $request->has('activa'),
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
        if ($agendamentos > 0) {
            return redirect()->route('backoffice.task_schedules.index')->with('error', 'Não é possível apagar: esta tarefa já está agendada em outro agendamento.');
        }
        $schedule->users()->detach();
        $schedule->delete();
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
