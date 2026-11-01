@extends('errors.brand')

@section('code', '401')
@section('title', 'लगइन आवश्यक छ')
@section('subtitle', 'Please log in to continue')
@section('message', 'यो पृष्ठ हेर्न पहिले लगइन गर्नुहोस्। Log in first to open this page.')

@section('actions')
    <a href="{{ url('/login') }}" class="err-button err-button--primary">Log in</a>
    <a href="{{ url('/') }}" class="err-button">गृहपृष्ठ (Home)</a>
@endsection
