<div>
    <!-- Simplified CSS -->
    <style>
        :root {
            --primary-color: #006064;
            --secondary-color: #4fc3f7;
            --success-color: #4caf50;
            --text-primary: #1e293b;
            --text-muted: #64748b;
            --background: #f8fafc;
        }

        body {
            background: var(--background);
            font-family: Arial, sans-serif;
        }

        .uk-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .uk-button {
            background: var(--primary-color);
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }

        .uk-button:hover {
            background: darken(var(--primary-color), 10%);
        }

        .uk-button-secondary {
            background: var(--secondary-color);
        }

        .uk-button-secondary:hover {
            background: darken(var(--secondary-color), 10%);
        }

        .member-card {
            border-left: 4px solid var(--secondary-color);
            padding: 15px;
            border-radius: 8px;
        }

        .status-alive, .status-deceased {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .status-alive {
            background: var(--success-color);
        }

        .status-deceased {
            background: var(--text-muted);
        }

        .timeline-container {
            padding: 20px 0;
        }

        .timeline-item {
            position: relative;
            padding-left: 30px;
            margin-bottom: 15px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--primary-color);
        }

        .timeline-dot {
            position: absolute;
            left: 5px;
            top: 5px;
            width: 12px;
            height: 12px;
            background: var(--primary-color);
            border-radius: 50%;
        }

        .timeline-content {
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .uk-input, .uk-select {
            border-radius: 8px;
            padding: 8px;
            border: 1px solid #ddd;
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>

    <!-- Main Content -->
    <div class="uk-container uk-padding">
        <!-- Header -->
        <div class="uk-text-center uk-margin-bottom">
            <h1 class="uk-heading-medium uk-text-primary">Family Legacy Explorer</h1>
            <p class="uk-text-muted">Discover your family history</p>
        </div>

        <!-- Search and Selection Card -->
        <div class="uk-card fade-in">
            <div class="uk-grid uk-grid-small" uk-grid>
                <div class="uk-width-1-2@m">
                    <label class="uk-form-label">Search Family</label>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="searchQuery" 
                        class="uk-input uk-width-1-1" 
                        placeholder="Search by name or relation..."
                    />
                    <div class="uk-margin-small-top uk-flex">
                        <button wire:click="filterByStatus('living')" class="uk-button uk-button-small">
                            <span class="status-alive"></span> Living
                        </button>
                        <button wire:click="filterByStatus('deceased')" class="uk-button uk-button-small uk-margin-small-left">
                            <span class="status-deceased"></span> Deceased
                        </button>
                    </div>
                </div>
                <div class="uk-width-1-2@m">
                    <label class="uk-form-label">Select Member</label>
                    <select wire:model="selectedMemberId" class="uk-select uk-width-1-1">
                        <option value="">Choose a family member...</option>
                        @foreach ($filteredMembers as $member)
                            <option value="{{ $member->id }}">{{ $member->name }} (Pusta: {{ $member->pustanumber }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="uk-flex uk-flex-center uk-margin-top">
                <button wire:click="resetSelection" class="uk-button uk-button-secondary">Reset</button>
                <button id="show-timeline" class="uk-button uk-margin-small-left">View Timeline</button>
            </div>

            <!-- Selected Member Info -->
            @if($selectedMemberId && $selectedMember = $filteredMembers->firstWhere('id', $selectedMemberId))
                <div class="member-card uk-margin-top">
                    <div class="uk-flex uk-flex-middle">
                        <div class="uk-width-auto">
                            @if($selectedMember->image && Storage::exists($selectedMember->image))
                                <img src="{{ Storage::url($selectedMember->image) }}" alt="{{ $selectedMember->name }}" class="uk-border-circle" width="80" height="80">
                            @else
                                <div class="uk-border-circle uk-background-primary uk-flex uk-flex-center uk-flex-middle" style="width: 80px; height: 80px;">
                                    <span uk-icon="icon: user; ratio: 2"></span>
                                </div>
                            @endif
                        </div>
                        <div class="uk-width-expand uk-margin-left">
                            <h3>{{ $selectedMember->name }}</h3>
                            <p>Pusta: {{ $selectedMember->pustanumber }} | 
                               Status: <span class="{{ $selectedMember->date_of_death ? 'status-deceased' : 'status-alive' }}"></span>
                               {{ $selectedMember->date_of_death ? 'Deceased' : 'Living' }}
                            </p>
                            @if($selectedMember->date_of_birth)
                                <p>Birth: {{ $selectedMember->date_of_birth->format('Y-m-d') }}</p>
                            @endif
                            @if($selectedMember->description)
                                <p>{{ Str::limit($selectedMember->description, 100) }}</p>
                            @endif
                            <button uk-toggle="target: #member-modal-{{ $selectedMember->id }}" class="uk-button uk-button-text">View Details</button>
                        </div>
                    </div>
                </div>

                <!-- Member Details Modal -->
                <div id="member-modal-{{ $selectedMember->id }}" uk-modal>
                    <div class="uk-modal-dialog uk-modal-body uk-border-rounded">
                        <button class="uk-modal-close-default" uk-close></button>
                        <h2>{{ $selectedMember->name }}</h2>
                        <div class="uk-grid-small" uk-grid>
                            <div class="uk-width-1-3">
                                @if($selectedMember->image && Storage::exists($selectedMember->image))
                                    <img src="{{ Storage::url($selectedMember->image) }}" alt="{{ $selectedMember->name }}" class="uk-border-rounded uk-width-1-1">
                                @else
                                    <div class="uk-background-primary uk-border-rounded uk-flex uk-flex-center uk-flex-middle" style="height: 150px;">
                                        <span uk-icon="icon: user; ratio: 3"></span>
                                    </div>
                                @endif
                            </div>
                            <div class="uk-width-2-3">
                                <p><strong>Birth:</strong> {{ $selectedMember->date_of_birth ? $selectedMember->date_of_birth->format('Y-m-d') : 'N/A' }}</p>
                                <p><strong>Death:</strong> {{ $selectedMember->date_of_death ? $selectedMember->date_of_death->format('Y-m-d') : 'N/A' }}</p>
                                <p><strong>Status:</strong> {{ $selectedMember->date_of_death ? 'Deceased' : 'Living' }}</p>
                                <p><strong>Pusta:</strong> {{ $selectedMember->pustanumber }}</p>
                                @if($selectedMember->description)
                                    <p><strong>Story:</strong> {{ $selectedMember->description }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Timeline View -->
        <div id="timeline-view" class="uk-card fade-in">
            <h2>Family Timeline</h2>
            <div class="timeline-container">
                @foreach ($filteredMembers as $member)
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <h4>{{ $member->name }}</h4>
                            <p>
                                @if($member->date_of_birth)
                                    Born: {{ $member->date_of_birth->format('Y-m-d') }}
                                @endif
                                @if($member->date_of_death)
                                    - Died: {{ $member->date_of_death->format('Y-m-d') }}
                                @endif
                            </p>
                            <button uk-toggle="target: #member-modal-{{ $member->id }}" class="uk-button uk-button-text">View Details</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Stats Footer -->
        <div class="uk-text-center uk-margin-top">
            <p><strong>{{ $filteredMembers->count() }}</strong> Family Members</p>
        </div>
    </div>

    <!-- Simplified JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('show-timeline')?.addEventListener('click', () => {
                const timeline = document.getElementById('timeline-view');
                timeline.style.display = timeline.style.display === 'none' ? 'block' : 'none';
            });
        });
    </script>
</div>