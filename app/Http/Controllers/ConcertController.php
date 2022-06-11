<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ConcertController extends Controller
{
    public function show($id): Factory|View|Application
    {
        $concert = Concert::published()->findOrFail($id);

        return view('concert.show', compact("concert"));
    }
}
