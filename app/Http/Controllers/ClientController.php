<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\LeadHistory;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'isActive']);
    }

    public function index()
    {
        $clients=Client::all();

        return view('backoffice.clients.index',compact('clients'));
    }
    public function create()
    {
        return view('backoffice.clients.create');
    }
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required',


        ]);

        $client = new Client();
        $client->name = $request->name;

        $client->save();


        flash(__('Client Created successfully!'))->success();

        return redirect()->route('backoffice.clients.index');



    }
    public function show($id)
    {
        $client=Client::find($id);
        if($client){
            return view('backoffice.clients.show',compact('client'));
        }
        else{
        flash(__('Não foi possivel encontrar o cliente'))->error();
        return view('backoffice.clients.index');
        }
    }


    public function edit($id)
    {
        $client=Client::find($id);
        if($client){
            return view('backoffice.clients.edit',compact('client'));
        }
        else{
        flash(__('Não foi possivel encontrar o cliente'))->error();
        return view('backoffice.clients.index');
        }

    }



    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required',


        ]);

        $client=Client::find($id);

        if($client){
            $client->name=$request->name;
            $client->update();
            flash(__('Cliente atualizado com sucesso'))->success();

        }
        else{
            flash(__('Não foi possivel encontrar o cliente'))->error();
        }
        return redirect()->route('backoffice.clients.index');
    }



    public function delete($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return redirect()->route('backoffice.clients.index')->with('error', 'Client not found.');
        }
        for($i=0;$i<count($client->contacts);$i++){
            $leadIds = Lead::where('contact_id', $client->contacts[$i]->id)->pluck('id');
            if($leadIds){
                Lead::whereIn('id', $leadIds)->delete();
                LeadHistory::whereIn('lead_id', $leadIds)->delete();
            }
        }
        $client->contacts()->delete();

        $client->delete();


        return redirect()->route('backoffice.clients.index')->with('success', 'Client and associated contacts deleted successfully.');
    }
}
