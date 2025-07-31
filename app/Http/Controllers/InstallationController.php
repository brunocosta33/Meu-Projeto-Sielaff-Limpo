<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Installation;

class InstallationController extends Controller
{   
    public function index()
    {
        $installations = Installation::with(['store', 'team'])
            ->orderBy('scheduled_date', 'desc')
            ->paginate(20);

        return view('backoffice.installations.index', compact('installations'));
    }

    public function create()
    {
        $stores = Store::all();
        $teams = Team::all();
        return view('backoffice.installations.create', compact('stores', 'teams'));
    }

    public function store(Request $request)
    {
        Installation::create($request->all());
        flash('Installation created successfully!')->success();
        return redirect()->route('backoffice.installations.index');
    }

}