<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\LeadHistory;

class LeadHistoryController extends Controller
{
    public function show($id)
    {
        $lead_hist=LeadHistory::find($id);

        return view('backoffice.leadHistory.show',compact('lead_hist'));
    }
}
