@extends('errors::minimal')

@section('title', __('Page Expired'))
@section('code', '419')
@section('message', __('Your session expired before the request finished'))
@section('description', __('This usually happens after waiting too long on a form or when the browser session token is no longer valid.'))
@section('primary_action_label', __('Back to home'))
@section('primary_action_href', url('/'))
@section('secondary_action_label', __('Log in again'))
@section('secondary_action_href', \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/'))
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
    <p>Refresh the page before submitting the form again.</p>
    <p>If you were logged in, signing in again can renew your session.</p>
    <p>Reopen the flow from the previous page if you were checking out or updating data.</p>
@endsection
