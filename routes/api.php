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
//Route::post('welcome',[ChatController::class,'welcome']);
Route::post('login',[AuthController::class,'login']);

Route::get('showProject/{id}',[ProjectsController::class,'show']);
Route::get('showAllProjects',[ProjectsController::class,'index']);
Route::get('image/{image_name}',[ProjectsController::class,'showImage']);
//Route::get('Allimage/{proID}',[ProjectsController::class,'showAllImage']);
//image 
Route::get('imageChat/{image_name}',[ChatController::class,'showImageChat']);

Route::middleware('auth:sanctum')->group(function()
{
    Route::post('logout',[AuthController::class,'logout']);
    Route::post('change_password',[AuthController::class,'change_password']);
    Route::post('update_profile',[AuthController::class,'update_profile']);

    //sentMessage
    Route::post('sentMessage',[ChatController::class,'sentMessage']);
    //ShowConvID
    Route::post('allMssageConvID/{id}',[ChatController::class,'allMssageConvID']);
    //markAsRead
    Route::post('markAsRead/{id}',[ChatController::class,'markAsRead']);
    Route::get('unread/{id}',[ChatController::class,'unread']);
    

});
Route::group([
    'prefix'=>'dashboard',
    'middleware'=>['auth:sanctum','privateAdmin'],
],function()
{  
Route::post('AddProject',[ProjectsController::class,'store']);
Route::post('updateProject/{id}',[ProjectsController::class,'update']);
Route::post('deleteProject/{id}',[ProjectsController::class,'destroy']);
 //showAllConv 
 Route::get('shoWAllConv',[ChatController::class,'shoWAllConv']);

});

Route::middleware(['auth:sanctum' , 'CheckAdmin'])->group(function()
{
    Route::post('dashboard',function() {

    });
});