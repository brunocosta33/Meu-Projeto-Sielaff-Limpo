<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::whereNull('deleted_at')->get();
        return view('backoffice.teams.index', compact('teams'));
    }

    public function create()
    {
        return view('backoffice.teams.create');
    }

    public function store(Request $request)
    {
        Team::create($request->all());
        flash('Equipa criada com sucesso!')->success();
        return redirect()->route('backoffice.teams.index');
    }

    public function edit($id)
    {
        $team = Team::findOrFail($id);
        return view('backoffice.teams.edit', compact('team'));
    }

    public function update(Request $request, $id)
    {
        $team = Team::findOrFail($id);
        $team->update($request->all());
        flash('Equipa atualizada com sucesso!')->success();
        return redirect()->route('backoffice.teams.index');
    }

    public function delete($id)
    {
        $team = Team::findOrFail($id);
        $team->delete();
        flash('Equipa removida com sucesso!')->success();
        return redirect()->route('backoffice.teams.index');
    }
}
