<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;

class TaskController extends Controller
{
    public function create()
    {
        $task = null; // <- isto resolve o problema
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
        $tasks = Task::all();
        return view('backoffice.tasks.index', compact('tasks'));
    }
}