<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\ChatController;
use  App\Http\Controllers\AuthController;
use  App\Http\Controllers\ProjectsController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register',[AuthController::class,'register']);
Route::post('login',[AuthController::class,'login']);

Route::get('showProject/{id}',[ProjectsController::class,'show']);
Route::get('showAllProjects',[ProjectsController::class,'index']);

Route::middleware('auth:sanctum')->group(function()
{
    Route::post('logout',[AuthController::class,'logout']);
    Route::post('change_password',[AuthController::class,'change_password']);
    Route::post('update_profile',[AuthController::class,'update_profile']);
    Route::post('sentMessage',[ChatController::class,'sentMessage']);

});
Route::group([
    'prefix'=>'dashboard',
    'middleware'=>['auth:sanctum','privateAdmin'],
],function()
{  
Route::post('AddProject',[ProjectsController::class,'store']);
Route::post('updateProject/{id}',[ProjectsController::class,'update']);
Route::post('deleteProject/{id}',[ProjectsController::class,'destroy']);
});