<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReturnText;


class ReturnTextController extends Controller
{


    public function index()
    {
        $text=ReturnText::all();

        return view('backoffice.returnText.index',compact('text'));
    }
    public function store(Request $request)
    {
        $msg=ReturnText::first();
        if($msg){
            $msg->response_text=$request->return_text;
            $msg->response_text_en=$request->return_text_en;
            $msg->update();

        }
        else{
            $msg= new ReturnText();
            $msg->response_text=$request->return_text;
            $msg->response_text_en=$request->return_text_en;
            $msg->save();
        }


        flash(__('Email atualizado com sucesso'))->success();


        return redirect()->route('backoffice.index');

    }





}
