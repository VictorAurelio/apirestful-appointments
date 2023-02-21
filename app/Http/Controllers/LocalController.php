<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\LocalAvaiability;
use App\Models\LocalQuality;
use App\Models\LocalTestimonial;
use App\Models\Local;
use App\Models\UserAppointment;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

use function PHPUnit\Framework\isEmpty;

class LocalController extends Controller
{
    private $isSignedIn;
    public function __construct() {
      $this->middleware('auth:api');
      $this->isSignedIn = Auth::user();
    }
    public function create(Request $request) {
      $array = ['error' => ''];      

      $validator = Validator::make($request->all(), [
        'name' => 'required',
        'maxquantity' => 'required|min:2|integer',
        'latitude' => 'string',
        'longitude' => 'string',
        'city' => 'required|string',
        'photos' => 'required|mimes:jpg, png, jpeg|max:2048'
      ]);
  
      if(!$validator->fails()) {
        $name = $request->input('name');
        $maxQuantity = $request->input('maxquantity');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $city = $request->input('city');        
        $image_path = $request->file('photos')->store('localphotos', 'public');

        $userId = $this->isSignedIn->id;
       
        $newLocal = new Local();
        $newLocal->name = $name;
        $newLocal->maxQuantity = $maxQuantity;
        $newLocal->latitude = $latitude;
        $newLocal->longitude = $longitude;
        $newLocal->city = $city;
        $newLocal->photos = $image_path;
        $newLocal->user_id = $userId;
        $newLocal->save();

      }else {
        $array['error'] = $validator->messages()->first();
        return $array;
      }
      return response()->json([
        'status' => 'success',
        'newLocal' => [
          'name' => $newLocal->name,
          'maxQuantity' => $newLocal->maxQuantity,
          'latitude' => $newLocal->latitude,
          'longitude' => $newLocal->longitude,
          'city' => $newLocal->city,
          'photos' => $newLocal->photos,
          'user_id' => $userId
        ]
      ]);
    }

    public function createUsersTestimonials(Request $request, $id) {
      $array = ['error' => ''];
      $local = Local::find($id);
      $userId = $this->isSignedIn->id;

      $userName = $this->isSignedIn->firstName . ' ' . $this->isSignedIn->lastName;

      $validator = Validator::make($request->all(), [
        'rate' => 'required|integer|max:5',
        'body' => 'required|string|max:600'
      ]);

      $alreadyReviewed = LocalTestimonial::where('user_id', $userId)
          ->where('local_id', $local->id)->first();

      if(!($local->user_id === $userId)) { 
        if($alreadyReviewed === null) {
          if(!$validator->fails()) {
            $rate = $request->input('rate');
            $body = $request->input('body');

            $newTestimonial = new LocalTestimonial();
            $newTestimonial->rate = $rate;
            $newTestimonial->body = $body;
            $newTestimonial->username = $userName;
            $newTestimonial->local_id = $local->id;
            $newTestimonial->user_id = $userId;
            $newTestimonial->save();

            $array['success'] = 'Depoimento adicionado com sucesso!';
          }else {
            $array['error'] = $validator->messages()->first();
            return $array;
          }
        }else {
          $array['error'] = 'Você já fez um depoimento.';
          return $array;
        }
      }else {
        $array['error'] = 'Você não pode fazer um depoimento em seu próprio local';
      }
      return $array;
    }

    private function googleGeo($address) {
      $key = env('MAPS_KEY', null);
      $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$address.'&key='.$key;
      $address = urlencode($address);

      $curlStart = curl_init();
      curl_setopt($curlStart, CURLOPT_URL, $url);
      curl_setopt($curlStart, CURLOPT_RETURNTRANSFER, 1);
      $answer = curl_exec($curlStart);
      curl_close($curlStart);

      return json_decode($answer, true);      
    }
    public function listAllLocals(Request $request) {   
      $userId = $this->isSignedIn->id;
      
      $usersTestimonials = LocalTestimonial::all();
      $thisUserTestimonial = LocalTestimonial::where('user_id', '=', $userId)->first();

      $lat = $request->input('lat');
      $lng = $request->input('lng');
      $city = $request->input('city');
      $offset = $request->input('offset');
      if(!$offset) {
        $offset = 0;
      }

      if(!empty($city)) {
        $result = $this->googleGeo($city);

        if(count($result['results']) > 0) {
          $lat = $result['results'][0]['geometry']['location']['lat'];
          $lng = $result['results'][0]['geometry']['location']['lng'];
        }
      }else if(!empty($lat) && (!empty($lng))) {
        $result = $this->googleGeo($lat . ',' . $lng);
        
        if(count($result['results']) > 0) {
          $city = $result['results'][0]['formatted_address'];
        }
      }else {
        $lat = '-16.328547';
        $lng = '-48.953403';
        $city = 'Anápolis';
      }

      $locals = Local::select(
      Local::raw
        ('*, SQRT(
             POW(69.1 * (latitude - '.$lat.'), 2) +
             POW(69.1 * ('.$lng.' - longitude) 
             * COS(latitude / 57.3), 2)) AS distance'
        ))->havingRaw('distance < ?', [20])
          ->orderBy('distance', 'ASC')
          ->offset($offset)
          ->limit(3)
          ->get();
      
      if($locals->count() > 0) {
        $array['data'] = $locals;
        $array['usersTestimonials'] = $usersTestimonials;
        $array['thisUserTestimonial'] = $thisUserTestimonial;
        $array['loc'] = 'Anápolis';

        return $array;
      }else {
        return $array['error'] = 'Não tem nenhum local cadastrado.';
      }
    }
    private function reviews($id) {
      $array = ['error' => ''];

      $rating = LocalTestimonial::select('*', 'rate')->where('local_id', $id)->avg('rate');
      $array = [
        'avg' => $rating //não está calculando a média corretamente
      ];

      return $array;
    }

    public function setAvailability(Request $request, $id) {
      $array = ['error' => ''];
      $local = Local::find($id);
      $userId = $this->isSignedIn->id;
      //Apenas quem cadastrou o local pode dizer quando estará disponível e por quanto tempo
      if($local->user_id === $userId) {
        $validator = Validator::make($request->all(), [
          'weekday' => 'required|integer|max:6', // 0- segunda, 1-terça, 2-quarta, 3-quinta, 4-sexta, 5-sabado, 6-domingo
          'hours' => 'integer|max:4', //Tempo da reserva
          'start_time' => 'required|date_format:H:i', //A partir de que horas estará disponível
          'end_time' => 'date_format:H:i|after:start_time' //Até que horas se pode fazer reserva
        ]);
        
        if(!$validator->fails()) {
          $weekday = $request->input('weekday');
          $hours = $request->input('hours');
          $start_time = $request->input('start_time');
          $end_time = $request->input('end_time');
          $newReservation = new LocalAvaiability();
          $newReservation->local_id = $local->id;
          $newReservation->hours = $hours;
          $newReservation->weekday = $weekday;
          $newReservation->start_time = $start_time;
          $newReservation->end_time = $end_time;
          $newReservation->save();

          $array['success'] = 'Disponibilidade adicionada com sucesso.';

          return $array;
        }else {
          $array['error'] = $validator->messages()->first();
          return $array;
        }
      }else {
        $array = ['error' => 'Usuário inválido!'];
        return $array;
      }
    }
    public function localQuality(Request $request, $id) {
      $array = ['error' => ''];
      $local = Local::find($id);
      $userId = $this->isSignedIn->id;

      if($local->user_id === $userId) {
        $validator = Validator::make($request->all(), [
          'description' => 'required',
          'price' => 'required',
        ]);

        if(!$validator->fails()){
          $description = $request->input('description');
          $price = $request->input('price');

          $locQuality = new LocalQuality();
          $locQuality->description = $description;
          $locQuality->price = $price;
          $locQuality->local_id = $local->id;
          $locQuality->save();

          $array = 'Descrição e preços adicionados com sucesso.';

          return $array;
        }else {
          $array = $validator->messages()->first();
          return $array;
        }

      }else {
        $array = ['error' => 'Usuário inválido!'];
        return $array;
      }

    }

    public function showOneLocal($id) {
      $local = Local::find($id);

      if($local) {
        $local['photos'] = url($local['photos']);
        $local['quality'] = [];
        $local['available'] = [];
        $local['reviews'] = [];
        $local['testimonials'] = [];

        $local['testimonials'] = LocalTestimonial::select('id', 'username', 'rate', 'body')
          ->where('local_id', $local->id)->get();

        $local['reviews'] = $this->reviews($local->id);

        $local['quality'] = LocalQuality::select('id', 'description', 'price')
          ->where('local_id', $local->id)->get();

        //Disponibilidade do local
        $availability = [];

        //Pegando a disponibilidade crua
        $avails = LocalAvaiability::where('local_id', $local->id)->get();
        $availWeekdays = [];

        foreach($avails as $item) {
          $availWeekdays[$item['weekday']] = explode(',', $item['start_time']);
        }

        //Pegando as reservas dos próximos dias
        $appointments = [];
        $appQuery = UserAppointment::where('local_id', $local->id)
          ->whereBetween('ap_datetime', [
            date('Y-m-d').' 00:00:00',
            date('Y-m-d', strtotime('+5 days')).' 23:59:59'
          ])
          ->get();
        foreach($appQuery as $appItem) {
          $appointments[] = $appItem['ap_datetime'];
        }
        //Gerar disponibilidade
        for($d=0;$d<5;$d++) {
          $timeItem = strtotime('+'.$d.'days');
          $weekday = date('w', $timeItem);

          if(in_array($weekday, array_keys($availWeekdays))) {
            $hours = [];

            $dayItem = date('Y-m-d', $timeItem);

            foreach($availWeekdays[$weekday] as $hourItem) {
              $dayFormated = $dayItem . ' ' . $hourItem . ':00';
              if(!in_array($dayFormated, $appointments)) {
                $hours[] = $hourItem;
              }
            }

            if(count($hours) > 0) {
              $availability[] =[
                'date' => $dayItem,
                'start_time' => $hours
              ];
            }
          }
        }



        $local['available'] = $availability;

        $array['data'] = $local;
      }else {
        return $array['error'] = 'Este local não existe.';
      } 
      
      return $array;
    }

    public function setAppointment(Request $request, $id) {
      //Local, data (y-m-d-h)
      $array = ['error' => ''];
      $local = Local::find($id);

      $validator = Validator::make($request->all(), [    
        'year' => 'required|integer',
        'month' => 'required|integer|max:12',
        'day' => 'required|integer',
        'hour' => 'required|date_format:H:i'
      ]);

      //Verificar se o local existe
      if($local && !$validator->fails()) {
        $year = $request->input('year');
        $month = $request->input('month');
        $day = $request->input('day');        
        $hour = $request->input('hour');
  
        $month = ($month < 10) ? '0'.$month : $month;
        $day = ($day < 10) ? '0'.$day : $day;
        $hour = ($hour < 10) ? '0'.$hour : $hour;
        
        //Verificar se a data é real
        $apDate = $year . '-' . $month . '-'. $day . ' ' . $hour. ':00';
        if(strtotime($apDate) > 0) {
          //Verificar se já existe uma reserva na data especificada
          $appointments = UserAppointment::select()
            ->where('local_id', $id)
            ->where('ap_datetime', $apDate)
            ->count();
          if($appointments === 0) {
            //Verificar se existe possibilidade de realizar reservas nesta data
            $weekday = date('w', strtotime($apDate));
            $availability = LocalAvaiability::select()
              ->where('local_id', $id)
              ->where('weekday', $weekday)
              ->first();
              if($availability) {
                //Verificar se o horário está disponível para reserva
                $hours = explode(',', $availability['start_time']);
                if(!in_array($hour.':00', $hours)) {
                  //Caso tudo esteja ok, realizar o agendamento.
                  $newAppointment = new UserAppointment();
                  $newAppointment->local_id = $id;
                  $newAppointment->user_id = $this->isSignedIn->id;
                  $newAppointment->ap_datetime = $apDate;
                  $newAppointment->save();
                  
                  $array['success'] = 'Agendamento realizado com sucesso';

                }else {
                  $array['error'] = 'Horário indisponível para reserva.';
                }
              }else {
                $array['error'] = 'Não existem horários disponíveis nesta data.';
              }
          }else { 
            $array['error'] = 'Já possui agendamento nesta data.';            
          }
        }else {
          $array['error'] = 'Data inválida.';
        }
      }else {
        $array['error'] = $validator->messages()->first();
      }      

      return $array;
    }

    public function search(Request $request) {
      $array = ['error' => '', 'list' => []];
      $q = $request->input('q');

      if($q) {
        $locals = Local::select()
          ->where('name', 'LIKE', '%'.$q.'%')
          ->get();

          foreach($locals as $locKey => $local) {
            $locals[$locKey]['photos'] = url('storage/'.$locals[$locKey]['photos']);
          }

          $array['list'] = $locals;
      }else {
        $array['error'] = 'Digite algo para buscar';
      }
      return $array;
    }

    public function update(Request $request, $id) {
      $array = ['error' => ''];
      $updatedLocalData = Local::find($id);
      $rules = [
        'name' => 'min:3',
        'city' => 'string|max:14',
        'latitude' => 'string',
        'longitude' => 'string',
        'maxquantity' => 'integer|min:2|numeric'
      ];
      
      $validator = Validator::make($request->all(), $rules);

      if($validator->fails()){
        $array['error'] = $validator->messages();
        return $array;
      }
      if($this->isSignedIn->id === $updatedLocalData->user_id) {
        $name = $request->input('name');
        $city = $request->input('city');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $maxquantity = $request->input('maxquantity');


        if($name) {
          $updatedLocalData->name = $name;
        }
        if($city) {
          $updatedLocalData->city = $city;
        }

        if($latitude) {
          $updatedLocalData->latitude = $latitude;
        }

        if($longitude) {
          $updatedLocalData->longitude = $longitude;
        }

        if($maxquantity) {
          $updatedLocalData->maxquantity = $maxquantity;
        }

        $updatedLocalData->save();

        $array['success'] = $validator->messages();

    }else {
      $array['error'] = 'Você não pode atualizar um cadastro que não é seu.';
    }

      $array['success'] = 'Campo(s) alterado(s) com sucesso.';
      return $array;
    }

    public function updatePhoto(Request $request, $id) {
      $array = ['error' => ''];
      $localNewImage = Local::find($id);

      $validator = Validator::make($request->all(), [
        'photos' => 'required|image|mimes:png,jpg,jpeg'
      ]);
      if($validator->fails()) {
        $array = $validator->messages();
        return $array;
      }
      if($this->isSignedIn->id === $localNewImage->user_id) {
        $locPhoto = $request->file('photos');
    
        $image_path = public_path('/storage/localphotos/');
        $imageName = md5(time().rand(0,9999)).'.jpg';
    
        $image = Image::make($locPhoto->getRealPath());
        $image->fit(300,300)->save($image_path.'/'.$imageName);
    
        $localNewImage->photos = $imageName;
        $localNewImage->save();
    
        $array['success'] = 'Imagem alterada.';
      }else {
        $array['error'] = 'Não pode alterar uma imagem de um local que não é seu.';
      }
  
      return $array;
    }

    public function delete($id) {
      $array = ['error' => ''];
      $local = Local::find($id);
      if($local) {
        if($local->user_id === $this->isSignedIn->id){
          $delLoc = Local::where('id', $id);
          $delLoc->delete();
          $array['success'] = 'Local deletado com sucesso.';
        }else{
          $array['error'] = 'Não é possível deletar um local que não é seu.';
          return $array;
        }
      }else {
        $array['error'] = 'Local inexistente.';
        return $array;
      }
      return $array;
    }
}
