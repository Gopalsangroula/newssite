</div>
<div class="uk-container uk-container-expand uk-margin-large-top uk-margin-large-bottom">

    <!-- Header Section -->
    <div class="uk-text-center uk-margin-large-bottom">
        <h1 class="uk-heading-primary uk-heading-medium@m uk-text-bold uk-margin-remove">वंशावली नम्बर वा नामबाट खोज्नुहोस्</h1>
    </div>

    <!-- Simple Search Section -->
    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-box-shadow-small uk-margin-bottom">
        <div class="uk-grid-medium uk-flex-middle" uk-grid>
            <div class="uk-width-expand">
                <div class="uk-inline uk-width-1-1">
                    <span class="uk-form-icon" uk-icon="icon: search"></span>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search"
                           placeholder="नाम, पुस्तक नं, वा पत्नीको नाम टाइप गर्नुहोस्..."
                           class="uk-input uk-form-large uk-form-medium@s"
                           autofocus>
                </div>
            </div>
            {{-- @if($search || $genderFilter || $birthDateFrom || $birthDateTo)
            <div class="uk-width-auto">
                <button class="uk-button uk-button-default uk-button-small" 
                        wire:click="resetFilters"
                        title="सबै फिल्टर हटाउनुहोस्">
                    <span uk-icon="icon: close; ratio: 0.8"></span> हटाउनुहोस्
                </button>
            </div>
            @endif --}}
        </div>
    </div>

    <!-- Grid View for Search Results -->
    @if($search && $searchResults && $searchResults->isNotEmpty())
        <!-- Grid Cards -->
<div class="uk-grid uk-child-width-1-2 uk-child-width-1-3@s uk-child-width-1-4@m uk-child-width-1-4@l" uk-grid>
    @foreach($searchResults as $person)
        <div>
            {{-- CORRECT: Use ID for the route --}}
            <a href="{{ route('frontend.genealogy.show', $person->id) }}" class="uk-card uk-card-default uk-card-hover uk-border-rounded uk-display-block">
                <div class="uk-card-media-top uk-text-center uk-padding-small">
                    @php
                        $imageUrl = $person->image 
                            ? asset('storage/' . $person->image) 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($person->name) . '&background=1e87f0&color=fff&bold=true&size=150';
                    @endphp
                    <img src="{{ $imageUrl }}" 
                         alt="{{ $person->name }}" 
                         class="uk-border-circle" 
                         style="width: 120px; height: 120px; object-fit: cover;"
                         loading="lazy">
                </div>
                <div class="uk-card-body uk-padding-small">
                    <h5 class="uk-card-title uk-text-medium uk-margin-remove-bottom uk-text-center uk-text-truncate">
                        {{ $person->name }}
                    </h5>
                    <div class="uk-text-center uk-text-muted uk-text-small">
                        <span class="uk-text-tiny">पु.नं: {{ $person->pustanumber }}</span>
                    </div>
                    <!-- Rest of your card content -->
                </div>
            </a>
        </div>
    @endforeach
</div>
        
        <!-- Grid Pagination -->
        @if ($searchResults->hasPages())

            <div class="uk-margin-large-top uk-margin-bottom">
                <ul class="uk-pagination uk-flex-center">
                    {{-- Previous Page --}}
                    @if ($searchResults->onFirstPage())
                        <li class="uk-disabled"><span><span uk-pagination-previous></span></span></li>
                    @else
                        <li><a href="#" wire:click.prevent="previousPage"><span uk-pagination-previous></span></a></li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($searchResults->getUrlRange(1, $searchResults->lastPage()) as $page => $url)
                        <li class="{{ $page == $searchResults->currentPage() ? 'uk-active' : '' }}">
                            <a href="#" wire:click.prevent="gotoPage({{ $page }})">{{ $page }}</a>
                        </li>
                    @endforeach

                    {{-- Next Page --}}
                    @if ($searchResults->hasMorePages())
                        <li><a href="#" wire:click.prevent="nextPage"><span uk-pagination-next></span></a></li>
                    @else
                        <li class="uk-disabled"><span><span uk-pagination-next></span></span></li>
                    @endif
                </ul>
                
                <div class="uk-text-center uk-text-small uk-text-muted uk-margin-small-top">
                    पृष्ठ {{ $searchResults->currentPage() }} of {{ $searchResults->lastPage() }}
                </div>
            </div>
        @endif
    @elseif($search && (!$searchResults || $searchResults->isEmpty()))
        <!-- Empty state for search -->
        <div class="uk-text-center uk-padding-large">
            <span uk-icon="icon: search; ratio: 3" class="uk-text-muted"></span>
            <h4 class="uk-text-muted">कुनै परिणाम फेला परेन</h4>
            <p class="uk-text-small uk-text-muted">कृपया आफ्नो खोज परिवर्तन गर्नुहोस्</p>
            <button class="uk-button uk-button-default uk-button-small uk-margin-top" wire:click="resetFilters">
                सबै फिल्टर हटाउनुहोस्
            </button>
        </div>
    @else
        <!-- Family Trees (Default View) -->

         <div class="uk-text-center uk-margin-medium-bottom uk-margin-large-top">
        <h1 class="uk-heading-primary uk-heading-medium@m uk-text-bold uk-margin-remove">केत्तकुम लिबाङ पाङ्पत सयङ वंशवृक्ष</h1>
    </div>

        <div class="genealogy-container uk-width-1-1">
            @foreach($roots as $rootIndex => $root)
                <div class="tree-wrapper uk-margin-xlarge-bottom uk-margin-medium-bottom@s" id="tree-{{ $rootIndex }}">
                    <div class="tree-scroll-container">
                        <div class="tree" id="tree-content-{{ $rootIndex }}">
                            <ul>
                                {!! $this->buildTreeNode($root, $people) !!}
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

       
    @endif

    <!-- Loading Indicator -->
    <div wire:loading class="uk-overlay-default uk-position-cover uk-flex uk-flex-center uk-flex-middle"
         style="z-index: 1000; position: fixed; top: 0; left: 0; right: 0; bottom: 0;">
        <div class="uk-text-center">
            <div uk-spinner="ratio: 2" class="uk-spinner-large@s"></div>
            <div class="uk-margin-small-top uk-text-small@s">लोड हुँदैछ...</div>
        </div>
    </div>

    <!-- Genealogy Tree JavaScript -->
    <script>
    function initGenealogyTree() {
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        // Build CSS selector for up to 12 nested generations
        let openLevelsSelector = [];
        let currentPath = '.tree > ul';
        
        for (let i = 1; i <= 12; i++) {
            currentPath += ' > li > ul.children-level';
            openLevelsSelector.push(currentPath);
        }

        const openLevelsQuery = openLevelsSelector.join(', ');

        // 1. Expand all branches up to the 12th level by default
        document.querySelectorAll(openLevelsQuery).forEach(ul => {
            ul.classList.remove('collapsed');
            
            const parentLi = ul.parentElement;
            const toggleBtn = parentLi ? parentLi.querySelector(':scope > .toggle-children') : null;
            
            if (toggleBtn) {
                toggleBtn.setAttribute('aria-expanded', 'true');
                const icon = toggleBtn.querySelector('.toggle-icon');
                if (icon) icon.classList.add('rotated');
            }
        });

        // 2. Collapse any levels deeper than 12
        document.querySelectorAll('.children-level').forEach(ul => {
            if (!ul.matches(openLevelsQuery)) {
                ul.classList.add('collapsed');
                const parentLi = ul.parentElement;
                const toggleBtn = parentLi ? parentLi.querySelector(':scope > .toggle-children') : null;
                if (toggleBtn) {
                    toggleBtn.setAttribute('aria-expanded', 'false');
                    const icon = toggleBtn.querySelector('.toggle-icon');
                    if (icon) icon.classList.remove('rotated');
                }
            }
        });

        // 3. Attach click event listeners safely
        document.querySelectorAll('.toggle-children').forEach(button => {
            if (button.dataset.bound === "true") return;
            button.dataset.bound = "true";

            const li = button.closest('li');
            const ul = li ? li.querySelector(':scope > ul.children-level') : null;
            const icon = button.querySelector('.toggle-icon');

            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (!ul) return;

                const isCollapsed = ul.classList.toggle('collapsed');
                if (icon) icon.classList.toggle('rotated', !isCollapsed);

                button.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
                button.setAttribute('uk-tooltip', isCollapsed ? 'Expand children' : 'Collapse children');

                if (typeof UIkit !== 'undefined' && !isMobile) {
                    UIkit.tooltip(button);
                }
            });
        });

        // 4. Center the view horizontally
        setTimeout(() => {
            const treeWrappers = document.querySelectorAll('.tree-wrapper');
            if (treeWrappers.length > 0 && !isMobile) {
                treeWrappers.forEach(wrapper => {
                    wrapper.scrollLeft = (wrapper.scrollWidth - wrapper.clientWidth) / 2;
                });
            }
        }, 300);
    }

    // Page load initialization
    document.addEventListener('DOMContentLoaded', initGenealogyTree);

    // Re-run script when Livewire updates components
    document.addEventListener('livewire:navigated', initGenealogyTree);
    if (window.Livewire) {
        Livewire.hook('commit', ({ respond, succeed }) => {
            succeed(() => {
                queueMicrotask(() => {
                    initGenealogyTree();
                });
            });
        });
    }
    </script>
<style>
    /* genealogy tree wrapper scroll fix */
    .genealogy-container {
        width: 100%;
        overflow: hidden;
    }

    .tree-wrapper {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        padding: 20px 0;
    }

    .tree-scroll-container {
        display: inline-block;
        min-width: 100%;
        text-align: center;
    }

    .tree {
        display: inline-block;
        white-space: nowrap;
        margin: 0 auto;
        padding: 20px;
    }

    .tree ul {
        padding-top: 20px;
        position: relative;
        transition: all 0.5s;
        display: flex;
        justify-content: center;
        padding-left: 0;
    }

    .tree li {
        float: left;
        text-align: center;
        list-style-type: none;
        position: relative;
        padding: 20px 5px 0 5px;
        transition: all 0.5s;
    }

    /* Grid View Cards */
    .uk-card-hover {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .uk-card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .uk-card-media-top img {
        transition: transform 0.3s;
    }

    .uk-card-hover:hover .uk-card-media-top img {
        transform: scale(1.05);
    }

    /* Search suggestions */
    .uk-label {
        cursor: pointer;
        transition: opacity 0.2s;
    }

    .uk-label:hover {
        opacity: 0.8;
    }

    /* Filter badges */
    .uk-badge {
        font-size: 0.75rem;
        padding: 4px 10px;
    }

    /* Responsive grid adjustments */
    @media (max-width: 640px) {
        .uk-child-width-1-2 > * {
            width: 50%;
        }
    }

    @media (min-width: 640px) and (max-width: 960px) {
        .uk-child-width-1-3\@s > * {
            width: 33.33%;
        }
    }

    @media (min-width: 960px) and (max-width: 1200px) {
        .uk-child-width-1-4\@m > * {
            width: 25%;
        }
    }

    @media (min-width: 1200px) {
        .uk-child-width-1-5\@l > * {
            width: 20%;
        }
    }

    /* Person card details styling */
    .uk-card-body .uk-text-truncate {
        max-width: 100%;
    }

    .uk-label-primary {
        background-color: #1e87f0;
    }

    .uk-label-danger {
        background-color: #f0506e;
    }

    .uk-label-warning {
        background-color: #faa05a;
        color: #fff;
    }

    /* Loading indicator */
    .uk-overlay-default {
        background: rgba(255,255,255,0.8);
        backdrop-filter: blur(3px);
    }
    </style>
</div>