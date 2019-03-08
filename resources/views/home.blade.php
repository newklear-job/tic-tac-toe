@extends('layouts.app')

@section('content')
    {{--<p>This users are online: </p>--}}
    {{--<ul>@foreach ($activities as $activity)--}}
            {{--@if (Auth::user()->id != $activity->user->id)--}}
                {{--<li>{{ $activity->user->name }}</li>--}}
            {{--@endif--}}
        {{--@endforeach--}}
    {{--</ul>--}}

<div id="vueApp">

            <tic-tac-toe></tic-tac-toe>
</div>

@endsection






