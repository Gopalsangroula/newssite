{{-- Individual visits with IP addresses. Include only behind Visit::canViewDetails(). --}}
<div class="ah-table-wrap">
    <table class="uk-table uk-table-divider ah-table">
        <thead>
            <tr>
                <th>Time</th>
                <th>IP address</th>
                <th>Page or post</th>
                <th>Device</th>
                <th>Browser</th>
                <th>Referrer</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($visits as $visit)
                <tr wire:key="visit-{{ $visit->id }}">
                    <td class="uk-text-nowrap">
                        <time datetime="{{ $visit->created_at->toIso8601String() }}" title="{{ $visit->created_at->format('M j, Y g:i A T') }}">{{ $visit->created_at->diffForHumans() }}</time>
                    </td>
                    <td class="ah-ip">{{ $visit->ip_address ?? 'Unknown' }}</td>
                    <td class="ah-visit-page">
                        @if ($visit->post)
                            <a href="{{ route('admin.posts.edit', $visit->post_id) }}" class="ah-table-title">{{ $visit->post->title }}</a>
                            <span class="ah-table-meta">{{ $visit->path }}</span>
                        @else
                            <span class="ah-table-title">{{ $visit->path }}</span>
                        @endif
                    </td>
                    <td>{{ ucfirst($visit->device) }}</td>
                    <td class="uk-text-nowrap">{{ $visit->browser }}</td>
                    <td class="ah-visit-referrer" title="{{ $visit->referrer }}">{{ $visit->referrer_label }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
