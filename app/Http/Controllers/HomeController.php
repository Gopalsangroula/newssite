<?php

namespace App\Http\Controllers;

use App\Support\HomeFeed;

class HomeController extends Controller
{
    public function index()
    {
        return view('welcome', (new HomeFeed)->data());
    }
}
