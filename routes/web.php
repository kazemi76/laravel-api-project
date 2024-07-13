<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
Route::get('/',function(){
return view('welcome');
});
Route::get('/payment/verify', function (Request $request) {
    $responce=Http::post('http://localhost:8000/api/payment/verify',[
        'token'=>$request->token,
        'status'=>$request->status
    ]);
    dd($responce->json());
});
