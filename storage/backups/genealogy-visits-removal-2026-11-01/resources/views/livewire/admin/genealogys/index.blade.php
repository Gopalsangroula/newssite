<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Genealogy</h1>
            <p class="ah-page-sub">{{ number_format($members->total()) }} {{ Str::plural('member', $members->total()) }}</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('admin.genealogys.create') }}" class="uk-button uk-button-primary">
                <span uk-icon="icon: plus; ratio: 0.8" wire:ignore></span>
                <span>Add Member</span>
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @foreach (['message' => 'uk-alert-success', 'error' => 'uk-alert-danger'] as $type => $class)
        @if (session($type))
            <div class="{{ $class }} ah-flash" uk-alert role="alert">
                <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
                <p>{{ session($type) }}</p>
            </div>
        @endif
    @endforeach

    <div class="ah-card">
        {{-- Filters --}}
        <div class="ah-toolbar">
            <div class="ah-toolbar-grow">
                <input
                    wire:model.live.debounce.500ms="search"
                    class="uk-input"
                    type="text"
                    placeholder="Search by name, pusta number, mother or wife..."
                    aria-label="Search members"
                >
            </div>
            <select wire:model.live="generationFilter" class="uk-select" aria-label="Filter by generation">
                <option value="">All Generations</option>
                @foreach ($generations as $generation)
                    <option value="{{ $generation }}">Generation {{ $generation }}</option>
                @endforeach
            </select>
            <select wire:model.live="sexFilter" class="uk-select" aria-label="Filter by sex">
                <option value="">All Sexes</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </div>

        {{-- Members Table --}}
        <div
            class="ah-table-wrap"
            wire:loading.class="ah-is-loading"
            wire:target="search,generationFilter,sexFilter,gotoPage,previousPage,nextPage"
        >
            <table class="uk-table uk-table-divider uk-table-hover uk-table-middle ah-table">
                <thead>
                    <tr>
                        <th class="ah-col-avatar"><span class="uk-hidden-visually">Photo</span></th>
                        <th class="ah-col-name">Name</th>
                        <th>Generation</th>
                        <th>Sex</th>
                        <th class="ah-col-name">Father</th>
                        <th class="ah-col-name">Mother</th>
                        <th>Dates</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        @php
                            $born = $member->date_of_birth?->format('M j, Y');
                            $died = $member->date_of_death?->format('M j, Y');
                        @endphp
                        <tr wire:key="member-{{ $member->id }}">
                            <td class="ah-col-avatar">
                                @if ($member->image)
                                    <img src="{{ Storage::url($member->image) }}" alt="" class="ah-avatar" width="36" height="36" loading="lazy" decoding="async">
                                @else
                                    <span class="ah-avatar ah-avatar--soft" aria-hidden="true">{{ Str::upper(Str::substr($member->name, 0, 1)) }}</span>
                                @endif
                            </td>
                            <td class="ah-col-name">
                                <a href="{{ route('admin.genealogys.update', $member->id) }}" class="ah-table-title">{{ $member->name }}</a>
                                @if ($member->wife_name)
                                    <span class="ah-table-meta">Wife: {{ $member->wife_name }}</span>
                                @endif
                            </td>
                            <td>{{ $member->pustanumber }}</td>
                            <td><span class="ah-badge">{{ ucfirst($member->sex) }}</span></td>
                            <td class="ah-col-name">
                                @if ($member->father_id)
                                    {{ $member->father->name }}
                                @else
                                    <span class="ah-muted">&mdash;</span>
                                @endif
                            </td>
                            <td class="ah-col-name">
                                @if ($member->mother_name)
                                    {{ $member->mother_name }}
                                @else
                                    <span class="ah-muted">&mdash;</span>
                                @endif
                            </td>
                            <td class="ah-col-date">
                                @if ($born && $died)
                                    {{ $born }} &ndash; {{ $died }}
                                @elseif ($born)
                                    Born {{ $born }}
                                @elseif ($died)
                                    Died {{ $died }}
                                @else
                                    &mdash;
                                @endif
                            </td>
                            <td>
                                <div class="ah-row-actions">
                                    <a href="{{ route('admin.genealogys.update', $member->id) }}">Edit</a>
                                    <button type="button" class="ah-action-danger" wire:click="confirmDelete({{ $member->id }})">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="ah-empty">No members found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($members->hasPages())
            <div class="ah-card-foot">
                {{ $members->links('partials.admin-pagination') }}
            </div>
        @endif
    </div>

    @include('partials.admin-delete-modal', ['title' => 'Delete Member', 'action' => 'deleteMember'])
</div>
