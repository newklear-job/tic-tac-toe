@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <div class="card">
                    <div class="card-header">Tic Tac Toe</div>

                    <div class="card-body">
                        <p>This users are online: </p>
                        <ul>
                        @foreach ($activities as $activity)
                            @if (Auth::user()->id != $activity->user->id)
                            <li>{{ $activity->user->name }}</li>
                            @endif
                        @endforeach
                        </ul>
                            <tic-tac-toe></tic-tac-toe>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection






