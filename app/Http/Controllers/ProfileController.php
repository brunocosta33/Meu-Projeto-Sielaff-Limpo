<?php

namespace App\Http\Controllers;

use App\Rules\Old_Password;
use Illuminate\Http\Request;
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

	/** Endereço antigo: o perfil é sempre o da página "Perfil". */
	public function myprofile()	{
		return redirect()->route('backoffice.profile.index');
	}

	/** Abre o perfil já com o formulário de mudar a password visível. */
	public function changePasswordForm()
	{
		return redirect()->route('backoffice.profile.index', ['password' => 1]);
	}

	/** Muda a password de quem tem a sessão iniciada (nunca de outro utilizador). */
	public function changePassword(Request $request)
	{
		$request->validate([
			'current' => ['required', new Old_Password],
			'password' => 'required|min:8|string',
			'password_confirmation' => 'required|min:8|string|same:password',
		]);

		$user = Auth::user();
		$user->password = bcrypt($request->password);
		$user->save();

		flash(__('Password successfully updated!'))->success();

		return redirect()->route('backoffice.profile.index');
	}
}
