<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $post->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/uikit@3.17.9/dist/css/uikit.min.css" rel="stylesheet">
</head>
<body>

    <div class="uk-container uk-container-large uk-padding">
        <h1 class="uk-heading-medium">{{ $post->title }}</h1>

        @if($post->cover)
            <img src="{{ asset('storage/' . $post->cover) }}" 
                 alt="{{ $post->title }}" 
                 class="uk-width-1-1 uk-margin-large-bottom">
        @endif

        <div class="uk-article">
            {!! $post->content !!}
        </div>
    </div>

</body>
</html>
