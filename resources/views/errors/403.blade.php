@extends('errors::minimal')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message', __('This area is off limits'))
@section('description', __('You reached a page that your current account is not allowed to access. If this seems unexpected, it may be a permissions issue.'))
@section('primary_action_label', __('Back to home'))
@section('primary_action_href', url('/'))
@section('secondary_action_label', __('Explore restaurants'))
@section('secondary_action_href', \Illuminate\Support\Facades\Route::has('restaurants.index') ? route('restaurants.index') : url('/'))
@section('visual')
    <div class="mx-auto flex w-full justify-center">
        <dotlottie-player
            src="https://lottie.host/3a816bf9-7811-47cd-9d2c-4c056b6257ce/EBJdiNthtu.lottie"
            background="transparent"
            speed="1"
            class="h-52 w-52 sm:h-52 sm:w-52"
            loop
            autoplay
        ></dotlottie-player>
    </div>
@endsection
@section('tips')
    <p>Double check that you are signed in with the right account.</p>
    <p>Owner and customer areas can have different access rules.</p>
    <p>If you should have access, ask an admin to review your permissions.</p>
@endsection
