<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.UserEvent.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('user.{userId}', function ($user, $userId) {
    if(Auth::id() === (int)$userId) {
        return ['id' => $user->id, 'name' => $user->name];
    }
    else {
        return false;
    }
});

Broadcast::channel('message.{roomId}', function ($user, $roomId) {
    return ['id' => $user->id, 'name' => $user->name];
});

Broadcast::channel('lobby', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});