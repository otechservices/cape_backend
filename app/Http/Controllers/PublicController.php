<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\ContactFormMail;


class PublicController extends Controller
{
    

    function sendContact(Request $request) {
        $data=$request->all();
        $to="masm.cape@gouv.bj";
        Mail::to($to)->send(new ContactFormMail($data));
    }
}
