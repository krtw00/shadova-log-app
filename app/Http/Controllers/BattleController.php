<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BattleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // For now, we'll just return the view.
        // Later, we can pass data like recent battles.
        return view('battles.index');
    }
}