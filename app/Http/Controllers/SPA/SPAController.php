<?php

namespace App\Http\Controllers\SPA;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SPAController
{
    function __invoke(Request $request): Factory|View|Application
    {
        request();
        return view('spa');
    }
}
