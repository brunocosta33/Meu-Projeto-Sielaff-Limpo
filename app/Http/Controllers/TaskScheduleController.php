<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\TaskSchedule;
use Illuminate\Http\Request;


class TaskScheduleController extends Controller
{
      public function index()
    {
        $schedules = TaskSchedule::with(['task', 'user'])->get();
        return view('task_schedule.index', compact('schedules'));
    }
    public function create()
    {
        $tasks = Task::all();
        $users = User::all();

        return view('task_schedule.create', compact('tasks', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'priority' => 'required|string|in:Alta,Média,Baixa',
            'deadline_date' => 'required|date',
            'deadline_time' => 'required',
            'is_active' => 'required|boolean',
            'is_group_task' => 'required|boolean',
            'is_repeating' => 'required|boolean',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        foreach ($request->user_ids as $user_id) {
            TaskSchedule::create([
                'task_id' => $request->task_id,
                'priority' => $request->priority,
                'deadline_date' => $request->deadline_date,
                'deadline_time' => $request->deadline_time,
                'is_active' => $request->is_active,
                'is_group_task' => $request->is_group_task,
                'is_repeating' => $request->is_repeating,
                'user_id' => $user_id,
            ]);
        }

        return redirect()->route('task_schedule.index')->with('success', 'Tarefa agendada com sucesso.');
    }

  
    public function edit($id)
    {
        $schedule = TaskSchedule::with('users')->findOrFail($id);
        $tasks = Task::all();
        $users = User::all();
        return view('backoffice.task_schedules.edit', compact('schedule', 'tasks', 'users'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'priority' => 'required|string|in:Alta,Média,Baixa',
            'deadline_date' => 'required|date',
            'deadline_time' => 'required',
            'is_active' => 'required|boolean',
            'is_group_task' => 'required|boolean',
            'is_repeating' => 'required|boolean',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $schedule = TaskSchedule::findOrFail($id);
        $schedule->update([
            'task_id' => $request->task_id,
            'priority' => $request->priority,
            'deadline_date' => $request->deadline_date,
            'deadline_time' => $request->deadline_time,
            'is_active' => $request->is_active,
            'is_group_task' => $request->is_group_task,
            'is_repeating' => $request->is_repeating,
        ]);

        $schedule->users()->sync($request->user_ids);

        return redirect()->route('backoffice.task_schedules.index')->with('success', 'Agendamento atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $schedule = TaskSchedule::findOrFail($id);
        $schedule->users()->detach();
        $schedule->delete();

        return redirect()->route('backoffice.task_schedules.index')->with('success', 'Agendamento removido com sucesso.');
    }
        public function minhasTarefas()
        {
            $user = auth()->user();

        $taskSchedules = $user->taskSchedules()
            ->with('task')
            ->orderBy('deadline_date')
            ->orderBy('deadline_time')
            ->get();

            return view('backoffice.tasks.minhas', compact('taskSchedules'));
        }

}
