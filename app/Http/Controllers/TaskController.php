<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;

class TaskController extends Controller
{
    public function myTasks()
    {
        return redirect()->route('backoffice.task_schedules.minhas');
    }

    public function show(Task $task)
    {
        return redirect()->route('backoffice.task_schedules.minhas')
            ->with('info', 'A página antiga de "Minhas Tarefas" foi unificada na lista de agendamentos.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        return redirect()->route('backoffice.task_schedules.minhas')
            ->with('info', 'Atualize o estado da tarefa através da página "Minhas Tarefas".');
    }

    public function update(Request $request, $id)
    {
        abort_if($this->isTechnician(), 403);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $task = Task::findOrFail($id);
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()->route('backoffice.tasks.index')->with('success', 'Tarefa atualizada com sucesso.');
    }
    

    public function edit($id)
    {
        abort_if($this->isTechnician(), 403);

        $task = Task::findOrFail($id);
        return view('backoffice.tasks.edit', compact('task'));
    }

    public function create()
    {
        abort_if($this->isTechnician(), 403);

        $task = null; 
        return view('backoffice.tasks.create', compact('task'));
    }

    public function store(Request $request)
    {
        abort_if($this->isTechnician(), 403);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        Task::create([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()->route('backoffice.tasks.index')->with('success', 'Tarefa criada com sucesso.');
    }

public function index()
{
    abort_if($this->isTechnician(), 403);

    $tasks = Task::where(function ($q) {
        // Aparece se não tiver nenhum agendamento
        $q->whereDoesntHave('schedules')
          // Ou aparece se pelo menos um agendamento ainda não estiver concluído
          ->orWhereHas('schedules.users', function ($uq) {
              $uq->whereNull('task_schedule_user.estado')
                 ->orWhere('task_schedule_user.estado', '!=', 'Concluída');
          });
    })
    ->orderBy('created_at', 'desc')
    ->paginate(15);

    return view('backoffice.tasks.index', compact('tasks'));
}

    public function destroy($id)
    {
        abort_if($this->isTechnician(), 403);

        $task = Task::findOrFail($id);
        // Verifica se a tarefa está agendada em algum TaskSchedule
        if ($task->schedules()->count() > 0) {
            return redirect()->route('backoffice.tasks.index')->with('error', 'Não é possível apagar: esta tarefa já está agendada.');
        }
        $task->delete();
        return redirect()->route('backoffice.tasks.index')->with('success', 'Tarefa apagada com sucesso.');
    }

    private function isTechnician(): bool
    {
        $user = auth()->user();

        return $user && $user->hasRole('user');
    }
}   
