{{-- Flash messages and validation errors for the post and page forms. --}}
@if (session('message'))
    <div class="uk-alert-success" uk-alert role="alert">
        <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
        <p>{{ session('message') }}</p>
    </div>
@endif

@if (session('error'))
    <div class="uk-alert-danger" uk-alert role="alert">
        <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
        <p>{{ session('error') }}</p>
    </div>
@endif

@if ($errors->any())
    <div class="uk-alert-danger" role="alert" uk-alert>
        <ul class="uk-list uk-margin-remove">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
