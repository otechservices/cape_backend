<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {


    return view('emails.base');
});



Route::get('/test-mail', function () {
    Mail::raw('Ceci est un test', function ($message) {
        $message->to('ornihouss1@gmail.com')
                ->subject('Test Email Laravel');
    });

    return 'Email envoyé';
});