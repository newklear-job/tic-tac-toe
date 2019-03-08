<?php

namespace App\Http\Controllers;

use App\Events\Lobby;
use App\Events\UserEvent;

use Illuminate\Http\Request;
use Auth;
use Debugbar;
class LobbyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function chatMessage(Request $request)
    {
        Debugbar::info('dsa');
        event(new Lobby(Auth::user(), $request->get('message')));
    }

    public function userMessage(Request $request)
    {
        event(new UserEvent(Auth::user(), $request->get('message'), $request->get('toUser')));
    }

}
