<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\State;
use App\Models\Contact;
use App\Models\Client;
use App\Models\LeadHistory;
use App\Models\ReturnText;
use App\Rules\ReCaptcha;
use Illuminate\Support\Facades\Mail;
use App\Mail\LeadMail;
use App\Mail\ReturnMail;
use App\Models\Product;



class LeadController extends Controller
{


    public function index()
    {
        $l = lead::find(1);
        
        $p = Product::find($l->relation_id);
        


        $leads=Lead::all();

        return view('backoffice.leads.index',compact('leads', 'p'));
    }

    public function show($id)
    {

        $lead = Lead::with('product')->find($id);


    if (!$lead) {
        return view('backoffice.leads.index');
    }

    $clientId = $lead->contact->client_id;

    $relatedLeads = Lead::whereHas('contact', function ($query) use ($clientId) {
        $query->where('client_id', $clientId);
    })->get();

    $leadHistory = LeadHistory::where('lead_id', $id)->orderBy('created_at','desc')->get();

    $states = State::all();



        return view('backoffice.leads.show',compact('lead','leadHistory','relatedLeads','states'));
    }

    public function create()
    {
        return view('backoffice.leads.create');
    }
    public function store(Request $request)
    {



        $request->validate([
            'name' => 'required|string|max:255',
            'telephone' => 'regex:/^[0-9]+$/|digits_between:9,14',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
            'is_authorized' => 'required',
            'g-recaptcha-response' => ['required', new ReCaptcha]
            
        ]);


       
        //Create LEAD
        $lead = new Lead();
        $lead->name = $request->name;
        $lead->is_authorized = $request->is_authorized;
        $lead->message = $request->message;
        $lead->type = $request->type;
        $lead->state_id = 1;


        if ($request->type == 'flyer' && $request->has('flyer_id')) {
            $lead->relation_id = $request->flyer_id;
        }
        
        if ($request->type == 'product' && $request->has('product_id')) {
            $lead->relation_id = $request->product_id;
        }
        
        if ($request->type == 'contact' && $request->has('contact_id')) {
            $lead->relation_id = $request->contact_id;
        }
        


        //Create Contact
        $contactByEmail = Contact::where('email', $request->email)->first();
        $contactByTelephone = Contact::where('telephone', $request->telephone)->first();

        if ($contactByEmail && $contactByTelephone) {
            $contactName = Contact::where('name', $request->name)->first();
                if( $contactName && $contactName->name != $request->name)
                    {
                        $c=new Contact();
                        $c->client_id=$contactByEmail->client_id;
                        $c->name=$request->name;
                        $c->email=$contactByEmail->email;
                        $c->telephone=$contactByEmail->telephone;
                    }
                    else{
                        $c= $contactName;
                    }
        } elseif ($contactByEmail) {
            $c=new Contact();
            $c->client_id=$contactByEmail->client_id;
            $c->name=$request->name;
            $c->email=$contactByEmail->email;
            $c->telephone=$request->telephone;

        } elseif ($contactByTelephone) {
            $c=new Contact();
            $c->client_id=$contactByTelephone->client_id;
            $c->name=$request->name;
            $c->email=$request->email;
            $c->telephone=$contactByTelephone->telephone;

        } else {
            $cl=new Client();
            $cl->name=$request->name;
            $cl->save();

            $c=new Contact();
            $c->client_id=$cl->id;
            $c->name=$request->name;
            $c->email=$request->email;
            $c->telephone=$request->telephone;
        }
        if($c){
            $c->save();
            $lead->contact_id=$c->id;
            $lead->save();
        }
        /*
        $email_sv="info@samoucovillage.com";

        Mail::to($email_sv)->send(new LeadMail([
            'title' => $lead->name,
            'subject' => 'Samouco Village Contacto Site',
            'email' => $c->email,
            'contact' => $c->telephone,
            'message' => $lead->message,

        ]));

        $msg=ReturnText::first();
        if($request->locale =='en'){
            $return_msg=$msg->response_text_en;
            $greeting="Hello ";
        }
        else{
            $return_msg=$msg->response_text;
            $greeting="Olá ";

        }
        Mail::to($c->email)->send(new ReturnMail([
            'title' => $greeting.$lead->name.',',
            'subject'=> 'Samouco Village Contacto',
            'message' => $return_msg,

        ]));*/

        flash(__('Enviado com sucesso'))->success();

        return redirect()->route('frontoffice.index');



    }



    public function edit($id)
    {
        //$state = State::find($id);

        return view('backoffice.leads.edit');
    }



    public function update(Request $request, $id)
    {

        $lead=Lead::find($id);

        if(!$lead){
            flash(__('Não foi possivel atualizar a lead!'))->error();
            return redirect()->route('backoffice.leads.index');

        }
        $lead_hist=new LeadHistory();
        $lead_hist->lead_id=$lead->id;
        $lead_hist->message=$request->lead_message;
        $lead_hist->state_id=$lead->state_id;
        $lead_hist->reply_by=$request->reply_by;

        $lead_hist->save();

        $lead->state_id=$request->state_id;
        $lead->update();

        if($request->send_email){
            Mail::to($lead->contact->email)->send(new ReturnMail([
                'title'=> '',
                'subject'=> 'Samouco Village Contacto',
                'message' => $request->lead_message,

            ]));
        }


        flash(__('Updated successfully!'))->success();

        return redirect()->route('backoffice.leads.index');
    }



    public function delete($id)
    {
        LeadHistory::where('lead_id', $id)->delete();
        Lead::destroy($id);
        return redirect()->route('backoffice.leads.index');
    }
}
