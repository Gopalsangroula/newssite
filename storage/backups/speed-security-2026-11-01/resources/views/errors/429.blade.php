@extends('errors.brand')

@section('code', '429')
@section('title', 'धेरै अनुरोध भयो')
@section('subtitle', 'Too many requests')
@section('message', 'छोटो समयमा धेरै अनुरोध आयो। केही बेर पर्खेर फेरि प्रयास गर्नुहोस्। Please wait a moment and try again.')

@section('actions')
    <button type="button" class="err-button err-button--primary" onclick="location.reload()">Try again</button>
    <a href="{{ url('/') }}" class="err-button">गृहपृष्ठ (Home)</a>
@endsection
