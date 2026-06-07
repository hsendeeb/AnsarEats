@extends('errors::minimal')

@section('title', __('Not Found'))
@section('code', '404')
@section('message', __('We could not find that page'))
@section('description', __('The link may be outdated, the page may have moved, or the restaurant you were looking for is no longer available.'))
@section('primary_action_label', __('Explore restaurants'))
@section('primary_action_href', \Illuminate\Support\Facades\Route::has('restaurants.index') ? route('restaurants.index') : url('/'))
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
    <p>Check the URL for a typo, especially if you typed it manually.</p>
    <p>Start again from the restaurant listing to find the right page.</p>
    <p>Try the previous page if you arrived here from an old link.</p>
@endsection
