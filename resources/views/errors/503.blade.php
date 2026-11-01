@extends('errors.brand')

@section('code', '503')
@section('title', 'मर्मत हुँदैछ')
@section('subtitle', 'We will be back shortly')
@section('message', 'वेबसाइट अहिले मर्मतमा छ। केही बेरपछि फेरि आउनुहोस्। The website is under maintenance.')

@section('actions')
    <button type="button" class="err-button err-button--primary" onclick="location.reload()">Try again</button>
@endsection
