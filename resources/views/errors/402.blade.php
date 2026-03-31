@extends('errors::minimal')

@section('title', __('Payment Required'))
@section('code', '402')
@section('message', __('This step needs a payment update'))
@section('description', __('The request cannot be completed until the required payment details are resolved. You can safely head back and try another route.'))
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
    <p>Check your payment details and try the request again.</p>
    <p>Return to the previous step if you were in the middle of checkout.</p>
    <p>Browse the app normally while the issue gets sorted out.</p>
@endsection
