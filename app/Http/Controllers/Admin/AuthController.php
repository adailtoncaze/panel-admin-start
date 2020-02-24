<?php

namespace PanelAdmin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PanelAdmin\Http\Controllers\Controller;
use PanelAdmin\Support\Message;
use PanelAdmin\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check() === true){
            return redirect()->route('admin.home');
        }
        return view('admin.index');
    }

    public function home()
    {

        return view('admin.dashboard');

    }

    public function login(Request $request)
    {
        if (in_array('', $request->only('email', 'password'))){
            $json['message'] = $this->message->error('Oops! Informe todos os dados para realizar o login.')->render();
            return response()->json($json);
        }

        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)){
            $json['message'] = $this->message->error('Oops! Informe um e-mail válido.')->render();
            return response()->json($json);
        }

        $credentials = [
          'email' => $request->email,
          'password' => $request->password
        ];

        if (!Auth::attempt($credentials)){
            $json['message'] = $this->message->error('Oops! Usuário ou Senha inválido.')->render();
            return response()->json($json);
        }

        $this->authenticated($request->getClientIp());
        $json['redirect'] = route('admin.home');
        return response()->json($json);

    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.login');
    }

    private function authenticated(string $ip){
        $user = User::where('id', Auth::user()->id);
        $user->update([
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $ip
        ]);
    }
}
