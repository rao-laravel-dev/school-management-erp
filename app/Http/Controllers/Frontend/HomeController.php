<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function HomeIndex()
    {
        return view('frontend.home.index');
    }
}
