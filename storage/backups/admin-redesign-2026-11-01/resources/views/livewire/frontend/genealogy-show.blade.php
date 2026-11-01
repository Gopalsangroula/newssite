<div class="uk-container uk-container-large uk-margin-large-top uk-margin-large-bottom">

    <!-- Breadcrumb -->
    @isset($breadcrumbs)
        <nav class="uk-margin-medium-bottom">
            <ul class="uk-breadcrumb">
                @foreach($breadcrumbs as $index => $crumb)
                    @if($crumb['url'] && !$loop->last)
                        <li><a href="{{ $crumb['url'] }}" class="uk-link-muted">{{ $crumb['name'] }}</a></li>
                    @else
                        <li><span class="uk-text-bold uk-text-primary">{{ $crumb['name'] }}</span></li>
                    @endif
                @endforeach
            </ul>
        </nav>
    @endisset

    <div class="uk-grid-medium uk-grid-match" uk-grid>
        <!-- Left Profile Sidebar -->
        <div class="uk-width-1-3@m">
            <div class="uk-card uk-card-default uk-border-rounded profile-card uk-flex uk-flex-column uk-flex-between">
                <div>
                    <!-- Profile Avatar Container -->
                    <div class="uk-card-media-top uk-text-center uk-padding-top">
                        <div class="avatar-wrapper uk-inline">
                            @if($genealogy->image)
                                <img src="{{ Storage::url($genealogy->image) }}" alt="{{ $genealogy->name }}"
                                     class="uk-border-circle uk-box-shadow-medium profile-avatar">
                            @else
                                <div class="uk-border-circle profile-avatar avatar-fallback uk-flex uk-flex-center uk-flex-middle uk-margin-auto
                                            {{ $genealogy->sex === 'male' ? 'bg-gradient-male' : 'bg-gradient-female' }}">
                                    <span class="avatar-initials">
                                        {{ Str::substr($genealogy->name, 0, 2) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Member Title & Badge -->
                    <div class="uk-card-body uk-text-center uk-padding-small">
                        <h2 class="uk-card-title uk-text-bold uk-margin-small-bottom">{{ $genealogy->name }}</h2>
                        
                        @if($genealogy->pustanumber)
                            <span class="custom-pusta-badge">
                                वंशावली नं: <span class="uk-text-bold">{{ $genealogy->pustanumber }}</span>
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Life Status Footer -->
                {{-- <div class="status-banner uk-text-center {{ $genealogy->is_living ? 'status-alive' : 'status-deceased' }}">
                    <span uk-icon="icon: {{ $genealogy->is_living ? 'heart' : 'historical' }}" class="uk-margin-xsmall-right"></span>
                    {{ $genealogy->life_status }}
                </div> --}}
            </div>
        </div>

        <!-- Right Detailed Information -->
        <div class="uk-width-2-3@m">
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
                @if($activeTabs['basic'])
                    <div class="uk-grid-divider uk-grid-medium" uk-grid>
                        
                        <!-- Personal Details -->
                        <div class="uk-width-1-2@s">
                            <h4 class="section-title">
                                <span uk-icon="icon: user" class="uk-margin-small-right text-accent"></span>
                                व्यक्तिगत विवरण
                            </h4>
                            <div class="info-grid uk-margin-top">
                                <div class="info-item">
                                    <span class="info-label">पूरा नाम</span>
                                    <span class="info-value uk-text-bold">{{ $genealogy->name }}</span>
                                </div>

                                <div class="info-item">
                                    <span class="info-label">लिंग</span>
                                    <div class="info-value">
                                        <span class="uk-badge {{ $genealogy->sex === 'male' ? 'uk-label-primary' : 'uk-label-success' }}">
                                            {{ $genealogy->sex === 'male' ? 'पुरुष' : 'महिला' }}
                                        </span>
                                    </div>
                                </div>

                                @if($genealogy->date_of_birth)
                                    <div class="info-item">
                                        <span class="info-label">जन्म मिति</span>
                                        <span class="info-value">
                                            {{ $genealogy->date_of_birth->format('j F Y') }}
                                            @if($genealogy->age)
                                                <span class="uk-text-meta">({{ $genealogy->age }} वर्ष)</span>
                                            @endif
                                        </span>
                                    </div>
                                @endif

                                @if($genealogy->date_of_death)
                                    <div class="info-item">
                                        <span class="info-label">मृत्यु मिति</span>
                                        <span class="info-value uk-text-danger uk-text-bold">
                                            {{ $genealogy->date_of_death->format('j F Y') }}
                                        </span>
                                    </div>
                                @endif

                                @if($genealogy->wife_name && $genealogy->sex === 'male')
                                    <div class="info-item">
                                        <span class="info-label">पत्नी</span>
                                        <span class="info-value uk-text-bold text-accent">{{ $genealogy->wife_name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Family Details -->
                        <div class="uk-width-1-2@s">
                            <h4 class="section-title">
                                <span uk-icon="icon: users" class="uk-margin-small-right text-accent"></span>
                                पारिवारिक जानकारी
                            </h4>
                            <div class="info-grid uk-margin-top">
                                @if($genealogy->father_id && $genealogy->father)
                                    <div class="info-item">
                                        <span class="info-label">पिता</span>
                                        <div class="info-value">
                                            <a href="{{ route('frontend.genealogy.show', ['genealogy' => $genealogy->father->id]) }}" 
                                            class="family-link">
                                                {{ $genealogy->father->name }}
                                                <span uk-icon="icon: arrow-right; ratio: 0.8"></span>
                                            </a>
                                            <div class="uk-text-meta">पु.नं: {{ $genealogy->father->pustanumber }}</div>
                                        </div>
                                    </div>
                                @endif

                                @if($genealogy->mother_name)
                                    <div class="info-item">
                                        <span class="info-label">माता</span>
                                        <span class="info-value uk-text-bold">{{ $genealogy->mother_name }}</span>
                                    </div>
                                @endif

                                <div class="info-item">
                                    <span class="info-label">सन्तान संख्या</span>
                                    <div class="info-value">
                                        <span class="uk-badge custom-count-badge">{{ $genealogy->children_count }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                @endif
            </div>
        </div>

        <!-- Lower Section: Additional Information -->
            @if($genealogy->description)
                <div class="uk-width-1-1">
                    <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
                        <h4 class="section-title">
                            <span uk-icon="icon: file-text" class="uk-margin-small-right text-accent"></span>
                            परिचय / चिनारी 
                        </h4>
                        <p class="uk-text-justify description-text uk-margin-top-small">
                            {{ $genealogy->description }}
                        </p>
                    </div>
                </div>
            @endif
    </div>

    <!-- Livewire Loading Overlay -->
    <div wire:loading class="uk-overlay-default uk-position-cover uk-flex uk-flex-center uk-flex-middle loading-overlay">
        <div class="uk-text-center">
            <div uk-spinner="ratio: 1.5"></div>
            <div class="uk-margin-small-top uk-text-bold">लोड हुँदैछ...</div>
        </div>
    </div>
</div>