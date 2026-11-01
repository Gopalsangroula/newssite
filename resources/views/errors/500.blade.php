@extends('errors.brand')

@section('code', '500')
@section('title', 'केही गडबड भयो')
@section('subtitle', 'Something went wrong on our side')
@section('message', 'यो समस्या तपाईंको कारणले होइन। केही बेरपछि फेरि प्रयास गर्नुहोस्। Please try again in a little while.')

@section('actions')
    <button type="button" class="err-button err-button--primary" onclick="location.reload()">Try again</button>
    <a href="{{ url('/') }}" class="err-button">गृहपृष्ठ (Home)</a>
@endsection
