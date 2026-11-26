<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function faq() {
        return view('pages.faq');
    }

    public function privacy() {
        return view('pages.privacy');
    }

    public function contact() {
        return view('pages.contact');
    }

    public function about() {
        return view('pages.about');
    }
}