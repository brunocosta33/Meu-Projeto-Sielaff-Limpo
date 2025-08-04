<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;

class TaskController extends Controller
{
    public function update(Request $request, $id)
    {
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
        $task = Task::findOrFail($id);
        return view('backoffice.tasks.edit', compact('task'));
    }

    public function create()
    {
        $task = null; 
        return view('backoffice.tasks.create', compact('task'));
    }

    public function store(Request $request)
    {
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
        $tasks = Task::orderBy('created_at', 'desc')->paginate(15);
        return view('backoffice.tasks.index', compact('tasks'));
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
        return redirect()->route('backoffice.tasks.index')->with('success', 'Tarefa apagada com sucesso.');
    }
}   