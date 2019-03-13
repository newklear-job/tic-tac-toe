<?php

namespace App\Http\Controllers;

use App\Events\NewStep;
use Auth;
use Illuminate\Http\Request;
use Debugbar;
class StepController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function post(Request $request)
    {
        event(new NewStep(Auth::user(), $request->get('message'), $request->get('room_id')));
    }
}