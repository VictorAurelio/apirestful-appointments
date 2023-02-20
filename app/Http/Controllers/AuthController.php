<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function PHPUnit\Framework\isEmpty;

class AuthController extends Controller
{
  public function __construct() {
    $this->middleware('auth:api', ['except' => ['login', 'create', 'unauthorized']]);
  }

  public function create(Request $request) {
    $array = ['error' => ''];

    $validator = Validator::make($request->all(), [
      'firstName' => 'required',
      'lastName' => 'required',
      'email' => 'required|email|unique:users',
      'password' => 'required|min:8|confirmed'
    ]);

    if(!$validator->fails()) {
      $firstName = $request->input('firstName');
      $lastName = $request->input('lastName');
      $email = $request->input('email');
      $password = $request->input('password');

      $hash = password_hash($password, PASSWORD_DEFAULT);

      $newUser = new User();
      $newUser->firstName = $firstName;
      $newUser->lastName = $lastName;
      $newUser->email = $email;
      $newUser->password = $hash;
      $newUser->save();

      $token = Auth::attempt([
        'email' => $email,
        'password' => $password
      ]);

      if(!$token) {
        $array['error'] = 'Ocorreu um erro, tente novamente.';
        return $array;
      }
      $info =  Auth::user();
      $info['avatar'] = url('media/avatars/'.$info['avatar']);
      $array['data'] = $info;
      $array['token'] = $token;
    }else {
      $array['error'] = $validator->messages()->first();
      return $array;
    }
    return $array;
  }

  public function login(Request $request) {
    $array = ['error' => ''];

    $validator = Validator::make($request->all(), [
      'email' => 'required|email',
      'password' => 'required|min:8'
    ]);

    $user = User::where('email', '=', $request->input('email'))->count();
    
    if(!$validator->fails()) {
     if($user != 0) {
      $email = $request->input('email');
      $password = $request->input('password');

      $token = Auth::attempt([
        'email' => $email,
        'password' => $password
      ]);

      if(!$token) {
        $array['error'] = $validator->messages()->first();
        return $array;
      }else {
        $info = Auth::user();
        $info['avatar'] = url('media/avatars/'.$info['avatar']);
        $array['data'] = $info;
        $array['token'] = $token;
      }
    }else {
        $array['error'] = 'Usuário não registrado!';
        return $array;
    }
    }else {
        $array['error'] = $validator->messages()->first();
        return $array;
    }
    return $array;
  }

  public function logout() {    
    $array = ['error' => ''];

    Auth::logout();

    if(isEmpty(User::all())){
      return $array['success'] = 'Deslogado com sucesso!';
    }else {
      return $array['error'] = 'Não foi possível deslogar, tente novamente.';
    }    
  }

  public function refresh() {
    $array = ['error' => ''];

    $token = Auth::refresh();
    $info = Auth::user();
    $info['avatar'] = url('media/avatars/'.$info['avatar']);
    $array['data'] = $info;
    $array['token'] = $token;    

    return $array;
  }

  public function unauthorized() {
    return response()->json([
      'error' => 'Acesso não autorizado'
    ], 401);
  }

  public function me() {
    $array = ['error' => ''];

    $user = Auth::user();
    $array = [
      'email' => $user->email,
      'firstName' => $user->firstName,
      'lastName' => $user->lastName,
      'avatar' => $user->avatar
    ];

    return $array;
    // return $array;
  } //metodo pra conferir os dados

}
