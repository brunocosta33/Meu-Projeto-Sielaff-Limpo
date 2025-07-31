<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
	public function __construct(){
		$this->middleware(['auth', 'isActive']);
	}

	public function index(){
		$user = Auth::user();
		return view('backoffice.profile.account',compact('user'));
	}

	public function myprofile()	{
		$user = Auth::user();
		return view('backoffice.profile.myaccount',compact('user'));
	}
}
