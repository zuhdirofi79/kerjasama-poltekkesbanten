<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function profil(): View
    {
        return view('pages.profil');
    }

    public function mitra(): View
    {
        return view('pages.mitra');
    }

    public function skema(): View
    {
        return view('pages.skema');
    }

    public function kontak(): View
    {
        return view('pages.kontak');
    }
}
