<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use App\Mail\LeadMail;

class MailController extends Controller
{
    public function index()
    {
        Mail::to('your_test_mail@gmail.com')->send(new LeadMail([
            'title' => 'The Title',
            'body' => 'The Body',
        ]));
    }
}
