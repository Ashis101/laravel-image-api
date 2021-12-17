<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\AlbumController;
use App\Http\Controllers\v1\ImageController;
use App\Http\Controllers\AuthController;
 

Route::prefix('v1')->group(function(){
    Route::post('signin',[AuthController::class,'signin'],'signin');
    Route::post('signup',[AuthController::class,'signup'],'signup');
    Route::post('/resetpassword/email',[AuthController::class,'isvalidemail']);
    Route::post('/resetpassword/password',[AuthController::class,'resetpassword']);
});


Route::group(['middleware'=>'auth:sanctum'],function(){
    Route::prefix('v1')->group(function(){
        Route::apiResource('album',AlbumController::class);
        Route::get('allimage',[ImageController::class,'index']);
        Route::get('image/by-album/{albumid}',[ImageController::class,'byalbum']);
        Route::get('image/{id}',[ImageController::class,'show']);
        Route::post('image/resize',[ImageController::class,'resize']);
        Route::delete('image/{imageid}',[ImageController::class,'destroy']);
        Route::post('logout',[AuthController::class,'logout']);
    });
});

