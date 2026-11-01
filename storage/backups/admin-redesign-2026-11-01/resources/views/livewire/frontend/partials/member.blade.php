<li>
    <div class="flex items-center space-x-2">
        @if($person->image)
            <img src="{{ asset('storage/' . $person->image) }}" class="w-10 h-10 rounded-full object-cover">
        @endif
        <div>
            <div class="font-medium">{{ $person->name }}</div>
            <div class="text-xs text-gray-500">{{ $person->pustanumber }}</div>
        </div>
    </div>

    @if($person->children()->count())
        <ul class="ml-6 mt-2">
            @foreach($person->children() as $child)
                @include('livewire.frontend.partials.member', ['person' => $child])
            @endforeach
        </ul>
    @endif
</li>
