<?php

namespace App\Http\Controllers;

use App\Models\CooperationSection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $sections = CooperationSection::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        return view('home', compact('sections'));
    }
}
