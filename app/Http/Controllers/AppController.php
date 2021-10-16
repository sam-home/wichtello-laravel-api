<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppController extends Controller
{
    public function home()
    {
        return view('home');
    }
    public function impressum()
    {
        return ['html' => (string)view('_impressum')];
    }

    public function datenschutz(Request $request)
    {
        if ($request->get('type') === 'json') {
            return ['html' => (string)view('_datenschutz')];
        }
        return view('datenschutz');
    }

    public function agb()
    {
        return ['html' => (string)view('_agb')];
    }
}
