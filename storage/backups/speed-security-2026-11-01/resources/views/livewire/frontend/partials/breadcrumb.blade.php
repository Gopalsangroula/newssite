{{-- $crumbs: list of ['name' => ..., 'url' => ...]. Crumbs past the second hide on phones. --}}
<nav class="lb-crumbs" aria-label="Breadcrumb">
    <ol>
        @foreach($crumbs as $crumb)
            <li @class(['is-middle' => $loop->index > 1 && ! $loop->last])>
                @if($crumb['url'] && ! $loop->last)
                    <a href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a>
                @else
                    <span aria-current="page">{{ $crumb['name'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
