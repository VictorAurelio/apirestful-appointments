<?php

namespace App\Http\Controllers;

use App\Models\Local;
use App\Models\User;
use App\Models\UserAppointment;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{
  private $isSignedIn;
  public function __construct() {
    $this->middleware('auth:api');
    $this->isSignedIn = Auth::user();
  }

  public function read() {
    $array = ['error' => ''];

    $info = $this->isSignedIn;
    $info = url('media/avatars/'.$info['avatar']);

    $array = [
      'firstName' => $this->isSignedIn->firstName,
      'lastName' => $this->isSignedIn->lastName,
      'email' => $this->isSignedIn->email,
      'avatar' => $info,
      'title' => 'Perfil do '. $this->isSignedIn->firstName. ' '. $this->isSignedIn->lastName,
    ];
    return view('user.userprofile', $array);
  }

  public function getAppointments() {
    $array = ['error' => '', 'list' => []];

    $appointments = UserAppointment::select()
      ->where('user_id', $this->isSignedIn->id)
      ->orderBy('ap_datetime', 'ASC')
    ->get();

    if($appointments) {
      foreach($appointments as $appointment) {
        $local = Local::find($appointment['local_id']);
        $local['photos'] = url('storage/'.$local['photos']);

        $array['list'][] = [
          'id' => $appointment['id'],
          'datetime' => $appointment['ap_datetime'],
          'local' => $local
        ];
      }
    }
    return $array;
  }

  public function update(Request $request) {
    $array = ['error' => ''];
    
    $rules = [
      'firstName' => 'min:2',
      'lastName' => 'min:2',
      'email' => 'email|unique:users',
      'password' => 'min:8|confirmed'
    ];
    
    $validator = Validator::make($request->all(), $rules);

    if($validator->fails()){
      $array['error'] = $validator->messages();
      return $array;
    }

    $firstName = $request->input('firstname');
    $lastName = $request->input('lastname');
    $email = $request->input('email');
    $password = $request->input('password');

    $updatedUserData = User::find($this->isSignedIn->id);
    if($firstName) {
      $updatedUserData->firstName = $firstName;
    }
    if($lastName) {
      $updatedUserData->lastName = $lastName;
    }

    if($email) {
      $updatedUserData->email = $email;
    }

    if($password) {
      $updatedUserData->password = password_hash($password, PASSWORD_DEFAULT);
    }

    $updatedUserData->save();

    $array['success'] = $validator->messages();
    return $array;
  }

  public function updateAvatar(Request $request) {
    $array = ['error' => ''];

    $validator = Validator::make($request->all(), [
      'avatar' => 'required|image|mimes:png,jpg,jpeg'
    ]);
    if($validator->fails()) {
      $array = $validator->messages();
      return $array;
    }
    $avatar = $request->file('avatar');

    $image_path = public_path('/media/avatars');
    $imageName = md5(time().rand(0,9999)).'.jpg';

    $image = Image::make($avatar->getRealPath());
    $image->fit(300,300)->save($image_path.'/'.$imageName);

    $userNewImage = User::find($this->isSignedIn->id);
    $userNewImage->avatar = $imageName;
    $userNewImage->save();

    $array['success'] = 'Imagem alterada.';

    return $array;
  }
}
