{{-- A sortable table heading. Expects $column and $label, and optionally $class. --}}
@php($sortedHere = $sortColumn === $column)
<th class="{{ $class ?? '' }}" aria-sort="{{ $sortedHere ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
    <button type="button" class="ahm-sort" wire:click="sortBy('{{ $column }}')">
        {{ $label }}
        @if ($sortedHere)
            <span aria-hidden="true">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
        @endif
    </button>
</th>
