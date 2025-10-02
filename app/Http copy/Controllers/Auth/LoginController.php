<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:admin', ['except' => 'logout']);
    }


    public function submit(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if (auth('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
            return redirect()->route('dashboard');
        }

        return redirect()->back()->withInput($request->only('email', 'remember'))
            ->withErrors(['Credentials does not match.']);
    }

    public function logout(Request $request)
    {
        auth()->guard('admin')->logout();
        return redirect()->route('login');
    }
}
