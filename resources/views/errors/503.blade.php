@extends('errors::minimal')

@section('title', __('Service Unavailable'))
@section('code', '503')
@section('message', __('The app is taking a short pause'))
@section('description', __('This part of the service is temporarily unavailable, often during maintenance or while the server is recovering.'))
@section('primary_action_label', __('Back to home'))
@section('primary_action_href', url('/'))
@section('secondary_action_label', __('Help center'))
@section('secondary_action_href', \Illuminate\Support\Facades\Route::has('help.center') ? route('help.center') : url('/'))
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
    <p>Wait a minute and try again once maintenance or recovery finishes.</p>
    <p>This error is usually temporary and should not require a permanent fix from the user.</p>
    <p>If it stays up too long, the deployment or server health should be checked.</p>
@endsection
