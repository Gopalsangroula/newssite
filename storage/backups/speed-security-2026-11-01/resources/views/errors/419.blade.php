@extends('errors.brand')

@section('code', '419')
@section('title', 'सत्र समाप्त भयो')
@section('subtitle', 'Your session has expired')
@section('message', 'लामो समय केही नगर्दा सत्र समाप्त भयो। पृष्ठ रिफ्रेस गरेर फेरि प्रयास गर्नुहोस्। Refresh the page and try again.')

@section('actions')
    <button type="button" class="err-button err-button--primary" onclick="location.reload()">Refresh</button>
    <a href="{{ url('/') }}" class="err-button">गृहपृष्ठ (Home)</a>
@endsection
