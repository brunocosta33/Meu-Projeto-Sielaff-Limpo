<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Client;
use App\Models\Lead;
use App\Models\LeadHistory;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;



class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'isActive']);
    }

    public function index()
    {
        $contacts=Contact::all();

        return view('backoffice.contacts.index',compact('contacts'));
    }
    public function create()
    {
        $clients=Client::all();
        return view('backoffice.contacts.create',compact('clients'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'telephone' => 'required|regex:/^[0-9]+$/|digits_between:9,14',
            'email' => 'required|email|max:255',
        ]);
        $contact= new Contact();
        if($request->client_id == null){
            $client=Client::where('name', $request->name)->first();
            if(!$client){
                $client= new Client();
                $client->name=$request->name;
                $client->save();
            }
            $contact->client_id=$client->id;
            $contact->name=$request->name;
            $contact->email=$request->email;
            $contact->telephone=$request->telephone;

        }
        else{
            $contact->client_id=$request->client_id;
            $contact->name=$request->name;
            $contact->email=$request->email;
            $contact->telephone=$request->telephone;

        }
        $contact->save();
        flash(__('Contacto criado com sucesso'))->success();

        return redirect()->route('backoffice.contacts.index');



    }
    public function show($id)
    {
        $contacts=Contact::find($id);

        return view('backoffice.contacts.show',compact('contacts'));
    }


    public function edit($id)
    {
        $contacts=Contact::find($id);
        return view('backoffice.contacts.edit',compact('contacts'));
    }



    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'telephone' => 'required|regex:/^[0-9]+$/|digits_between:9,14',
            'email' => 'required|email|max:255',
        ]);

        $contact=Contact::find($id);

        $contact->client_id=$request->client_id;
        $contact->name=$request->name;
        $contact->email=$request->email;
        $contact->telephone=$request->telephone;
        $contact->update();

        flash(__('Updated successfully!'))->success();

        return redirect()->route('backoffice.contacts.index');
    }
    public function delete($id)
    {
        $c=Contact::find($id);
        if(!$c){
            return redirect()->route('backoffice.contacts.index')->with('error', 'Contact not found.');
        }
        $lead=Lead::where('contact_id',$id)->get();
        if($lead){
                $leadIds = Lead::where('contact_id', $c->id)->pluck('id');
                if($leadIds){
                    LeadHistory::whereIn('lead_id', $leadIds)->delete();
                }
        }

        $c->leads()->delete();
        Contact::destroy($id);
        return redirect()->route('backoffice.contacts.index');
    }
}
