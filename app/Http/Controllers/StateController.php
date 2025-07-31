<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\State;

class StateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'isActive']);
    }

    public function index()
    {
        $states=State::all();

        return view('backoffice.state.index',compact('states'));
    }
    public function create()
    {
        return view('backoffice.state.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',


        ]);

        $state = new State();
        $state->name = $request->name;
        $state->desc = $request->desc;

        $state->save();


        flash(__('Created successfully!'))->success();

        return redirect()->route('backoffice.state.index');



    }
    public function show($id)
    {
        $state = State::find($id);

        return view('backoffice.state.show', compact('state'));
    }


    public function edit($id)
    {
        $state = State::find($id);

        return view('backoffice.state.edit', compact('state'));
    }



    public function update(Request $request, $id)
    {


        $state = State::find($id);
        $state->name = $request->name;
        $state->desc = $request->desc;

        $state->save();



        flash(__('Updated successfully!'))->success();

        return redirect()->route('backoffice.state.index');
    }



    public function delete($id)
    {
        State::destroy($id);
        return redirect()->route('backoffice.state.index');
    }
}
