{{-- What a file is attached to. Expects $media, with the mediable relation loaded. --}}
@php
    $parent = $media->mediable;
    $parentType = $media->mediable_type ? class_basename($media->mediable_type) : null;

    // The Pages section is limited to admins, so an editor would get a 403 from the link.
    $parentLink = match (true) {
        ! $parent => null,
        $parentType === 'Post' => route('admin.posts.edit', $parent->id),
        $parentType === 'Page' && auth()->user()?->hasAnyRole(['admin', 'super-admin']) => route('admin.pages.edit', $parent->id),
        default => null,
    };
@endphp

@if (! $media->mediable_type || ! $media->mediable_id)
    <span class="ahm-muted">Unattached</span>
@elseif (! $parent)
    <span class="ahm-muted">{{ $parentType }} #{{ $media->mediable_id }} (deleted)</span>
@elseif ($parentLink)
    <a href="{{ $parentLink }}">{{ $parent->title }}</a>
    <span class="ah-table-meta">{{ $parentType }}</span>
@else
    {{ $parent->title }}
    <span class="ah-table-meta">{{ $parentType }}</span>
@endif
