@extends('errors::minimal')

@section('title', __('Server Error'))
@section('code', '500')
@section('message', __('Something broke on our side'))
@section('description', __('The app ran into an unexpected problem while handling your request. The best next step is usually a refresh or a short retry later.'))
@section('primary_action_label', __('Back to home'))
@section('primary_action_href', url('/'))
@section('secondary_action_label', __('Try browsing again'))
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
    <p>Refresh once to rule out a temporary hiccup.</p>
    <p>If you were submitting data, make sure the previous action did not partially complete.</p>
    <p>Repeated 500 errors usually mean the server logs need attention.</p>
@endsection
