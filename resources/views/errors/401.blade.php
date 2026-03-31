@extends('errors::minimal')

@section('title', __('Unauthorized'))
@section('code', '401')
@section('message', __('Please sign in to continue'))
@section('description', __('This page needs an active account session before it can be opened. Log in and try the action again.'))
@section('primary_action_label', __('Log in'))
@section('primary_action_href', \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/'))
@section('secondary_action_label', __('Back to home'))
@section('secondary_action_href', url('/'))
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
    <p>Make sure you are signed in with the correct account.</p>
    <p>If your session timed out, logging in again should fix it.</p>
    <p>Use the home page if you want to keep browsing first.</p>
@endsection
