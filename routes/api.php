<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/ping', function() {
//   return ['pong' => true];
// }); apenas debug

//Caso não esteja logado, não pode realizar o acesso de algumas páginas/funcionalidades
Route::get('/401', [AuthController::class, 'unauthorized'])->name('login');

//Autenticação de login e logout
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
//Dar refresh no token do usuário -> não habilitei o refresh automático por tempo
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

// Route::get('/auth/me', [AuthController::class, 'me']); estava utilizando pra coletar as informações do usuário, antes de desenvolver o método específico para tal
//Realiza o cadastro do usuário
Route::post('/user', [AuthController::class, 'create']);

//Mostra e altera os dados do usuário -> criei uma view só pra ver a foto
Route::get('/user', [UserController::class, 'read']);
Route::put('/user', [UserController::class, 'update']);
//Metodo pra troca de foto do usuário -> através do put não funcionou, utilizei post e criei um novo método
Route::post('/user/avatar', [UserController::class,'updateAvatar']);

//faz uma busca pelos agendamentos do usuário
Route::get('/user/appointments', [UserController::class, 'getAppointments']);

//Realiza o cadastro de um local
Route::post('/local/create', [LocalController::class, 'create']);

//Adiciona um depoimento sobre um local,**cada usuário pode comentar apenas uma vez** - Quem criou o local não pode inserir um depoimento
Route::post('/local/{id}/testimonials', [LocalController::class, 'createUsersTestimonials']);

//Busca por todos os locais, ou um lugar específico
Route::get('/locals', [LocalController::class, 'listAllLocals']);
Route::get('/local/{id}', [LocalController::class, 'showOneLocal']);

//Adiciona alguma qualidade pro local (descrição, preços, promoções, etc)
Route::post('/local/{id}', [LocalController::class, 'localQuality']);

//Realiza a edição do local - apenas pra quem fez seu cadastro
Route::put('/local/{id}', [LocalController::class, 'update']);
//Realiza a alteração da imagem do local - apenas quem fez seu cadastro
Route::post('/local/{id}/photo', [LocalController::class, 'updatePhoto']);

//Buscar por um local
Route::get('/search', [LocalController::class, 'search']);

//Definir datas disponiveis (admin/usuario que criou o local) e realizar agendamento (qualquer usuario)
Route::post('/local/{id}/availability', [LocalController::class, 'setAvailability']);
Route::post('/local/{id}/appointment', [LocalController::class, 'setAppointment']);

//Deletar local e agendamento
Route::delete('/local/{id}', [LocalController::class, 'delete']);
Route::delete('/user/appointment/{id}', [UserController::class, 'deleteAppointment']);