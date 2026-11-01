{{--
    One person in the genealogy tree, followed by their descendants.
    Takes $person, $childrenByFather, $level and $openLevels. Branches deeper than
    $openLevels start closed; the script in genealogy-archive opens and closes them.
--}}
@php
    $treeChildren = $childrenByFather->get($person->id, collect());
    $isOpen = $level < $openLevels;
@endphp

<li class="lb-tree__item">
    <div class="lb-tree__node">
        <a class="lb-tree__card" href="{{ route('frontend.genealogy.show', $person->id) }}" title="{{ $person->name }}">
            @include('livewire.frontend.partials.avatar', [
                'name' => $person->name,
                'image' => $person->image,
                'sex' => $person->sex,
                'size' => 'tree',
            ])
            <span class="lb-tree__text">
                <span class="lb-tree__name">{{ $person->name }}</span>
                <span class="lb-tree__pusta">पुस्ता {{ $person->pustanumber }}</span>
            </span>
        </a>

        @if($treeChildren->isNotEmpty())
            <button type="button" class="lb-tree__toggle" data-tree-toggle
                    aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                    aria-label="{{ $treeChildren->count() }} सन्तान देखाउनुहोस् वा लुकाउनुहोस्"
                    title="{{ $treeChildren->count() }} सन्तान">
                @if($treeChildren->count() > 1)
                    <span class="lb-tree__count">{{ $treeChildren->count() }}</span>
                @endif
            </button>
        @endif
    </div>

    @if($treeChildren->isNotEmpty())
        <ul @class(['lb-tree__branch', 'lb-tree__branch--single' => $treeChildren->count() === 1]) @unless($isOpen) hidden @endunless>
            @foreach($treeChildren as $child)
                @include('livewire.frontend.partials.tree-node', ['person' => $child, 'level' => $level + 1])
            @endforeach
        </ul>
    @endif
</li>
