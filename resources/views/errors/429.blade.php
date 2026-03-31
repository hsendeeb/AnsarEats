@extends('errors::minimal')

@section('title', __('Too Many Requests'))
@section('code', '429')
@section('message', __('Too many requests in a short time'))
@section('description', __('You have hit a rate limit for this action. Give it a moment, then try again once the cooldown passes.'))
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
    <p>Wait a little before repeating the same action again.</p>
    <p>Avoid refreshing rapidly or submitting the same form multiple times.</p>
    <p>If the limit feels wrong, it may be worth checking server-side throttling rules.</p>
@endsection
