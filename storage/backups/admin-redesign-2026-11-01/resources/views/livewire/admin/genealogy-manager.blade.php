<div>
    <div class="uk-container uk-padding">
        <!-- Header -->
        <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
            <h1 class="uk-heading-small uk-margin-remove">Genealogy Admin</h1>
            <div>
                <input wire:model.live.debounce.500ms="search" type="text" placeholder="Search name or pusta number"
                       class="uk-input uk-width-medium" aria-label="Search members by name or pusta number" />
            </div>
        </div>

        <!-- Success and Error Messages -->
        @if(session()->has('message'))
            <div class="uk-alert uk-alert-success" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p>{{ session('message') }}</p>
            </div>
        @endif
        @if(session()->has('error'))
            <div class="uk-alert uk-alert-danger" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <div class="uk-grid uk-grid-medium" uk-grid>
            <!-- Form -->
            <div class="uk-width-1-3@m">
                <div class="uk-card uk-card-default uk-card-body">
                    <h2 class="uk-card-title">{{ $editingId ? 'Edit Member' : 'Add Member' }}</h2>

                    <form wire:submit.prevent="save" class="uk-form-stacked uk-margin-medium-top">
                        <div class="uk-margin">
                            <label class="uk-form-label" for="pustanumber">Pusta Number *</label>
                            <div class="uk-form-controls">
                                <input wire:model="pustanumber" id="pustanumber" class="uk-input" required />
                                @error('pustanumber') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="uk-margin">
                            <label class="uk-form-label" for="name">Name *</label>
                            <div class="uk-form-controls">
                                <input wire:model="name" id="name" class="uk-input" required />
                                @error('name') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="uk-grid uk-grid-small" uk-grid>
                            <div class="uk-width-1-2">
                                <label class="uk-form-label" for="sex">Sex</label>
                                <div class="uk-form-controls">
                                    <select wire:model="sex" id="sex" class="uk-select" aria-label="Select gender">
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="uk-width-1-2">
                                <label class="uk-form-label" for="image">Image</label>
                                <div class="uk-form-controls">
                                    <input wire:model="image" id="image" type="file" class="uk-input" accept="image/jpeg,image/png,image/jpg,image/gif" />
                                    @if($image)
                                        <img src="{{ $image->temporaryUrl() }}" class="uk-margin-small-top uk-border-rounded" style="height: 96px; object-fit: cover;" alt="Image preview" />
                                    @elseif($editingId && $existingImage = Genealogy::find($editingId)?->image)
                                        <img src="{{ asset('storage/' . $existingImage) }}" class="uk-margin-small-top uk-border-rounded" style="height: 96px; object-fit: cover;" alt="Current member image" />
                                    @endif
                                    @error('image') <span class="uk-text-danger uk-text-small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="uk-grid uk-grid-small" uk-grid>
                            <div class="uk-width-1-2">
                                <label class="uk-form-label" for="date_of_birth">Date of Birth</label>
                                <div class="uk-form-controls">
                                    <input wire:model="date_of_birth" id="date_of_birth" type="date" class="uk-input" max="{{ now()->format('Y-m-d') }}" />
                                </div>
                            </div>
                            <div class="uk-width-1-2">
                                <label class="uk-form-label" for="date_of_death">Date of Death</label>
                                <div class="uk-form-controls">
                                    <input wire:model="date_of_death" id="date_of_death" type="date" class="uk-input" max="{{ now()->format('Y-m-d') }}" />
                                </div>
                            </div>
                        </div>

                        <div class="uk-margin">
                            <label class="uk-form-label" for="father_id">Father</label>
                            <div class="uk-form-controls">
                                <select wire:model="father_id" id="father_id" class="uk-select" aria-label="Select father">
                                    <option value="">-- None --</option>
                                    @foreach($allMembers as $m)
                                        @if($m->sex === 'male')
                                            <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->pustanumber }})</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="uk-margin">
                            <label class="uk-form-label" for="mother_id">Mother</label>
                            <div class="uk-form-controls">
                                <select wire:model="mother_id" id="mother_id" class="uk-select" aria-label="Select mother">
                                    <option value="">-- None --</option>
                                    @foreach($allMembers as $m)
                                        @if($m->sex === 'female')
                                            <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->pustanumber }})</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="uk-margin">
                            <label class="uk-form-label" for="description">Description</label>
                            <div class="uk-form-controls">
                                <textarea wire:model="description" id="description" class="uk-textarea" rows="3" maxlength="1000" aria-label="Member description"></textarea>
                            </div>
                        </div>

                        <div class="uk-flex uk-flex-middle uk-grid-small" uk-grid>
                            <div>
                                <button type="submit" class="uk-button uk-button-primary" wire:loading.attr="disabled">
                                    {{ $editingId ? 'Update' : 'Create' }}
                                    <span wire:loading wire:target="save" class="uk-margin-small-left" uk-spinner="ratio: 0.6"></span>
                                </button>
                            </div>
                            <div>
                                <button type="button" wire:click="resetForm" class="uk-button uk-button-default">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Members List -->
            <div class="uk-width-2-3@m">
                <div class="uk-card uk-card-default uk-card-body uk-overflow-auto" style="max-height: 68vh;">
                    <h2 class="uk-card-title">Members</h2>
                    @if($members->isEmpty())
                        <div class="uk-alert uk-alert-warning" uk-alert>
                            <p>No members found.</p>
                        </div>
                    @else
                        <div class="uk-grid uk-grid-small uk-child-width-1-2@m" uk-grid>
                            @foreach($members as $member)
                                <div>
                                    <div class="uk-card uk-card-default uk-card-small uk-card-body uk-flex">
                                        <div class="uk-width-auto uk-margin-right">
                                            @if($member->image)
                                                <img src="{{ asset('storage/' . $member->image) }}" class="uk-border-rounded" style="width: 64px; height: 64px; object-fit: cover;" alt="{{ $member->name }}'s image" />
                                            @else
                                                <div class="uk-border-rounded uk-background-muted uk-flex uk-flex-center uk-flex-middle" style="width: 64px; height: 64px;">
                                                    <span class="uk-text-muted">No</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="uk-width-expand">
                                            <div class="uk-flex uk-flex-between uk-flex-middle">
                                                <div>
                                                    <strong>{{ $member->name }}</strong>
                                                    <div class="uk-text-small uk-text-muted">{{ $member->pustanumber }}</div>
                                                </div>
                                                <div class="uk-flex uk-flex-middle uk-grid-small" uk-grid>
                                                    <div>
                                                        <button wire:click="edit({{ $member->id }})" class="uk-button uk-button-text uk-text-warning" aria-label="Edit {{ $member->name }}">Edit</button>
                                                    </div>
                                                    <div>
                                                        <button class="uk-button uk-button-text uk-text-danger" uk-toggle="target: #delete-modal-{{ $member->id }}" aria-label="Delete {{ $member->name }}">Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="uk-text-small uk-margin-small-top uk-text-muted">
                                                {{ Str::limit($member->description, 120) }}
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Delete Confirmation Modal -->
                                    <div id="delete-modal-{{ $member->id }}" uk-modal>
                                        <div class="uk-modal-dialog uk-modal-body">
                                            <h2 class="uk-modal-title">Confirm Deletion</h2>
                                            <p>Are you sure you want to delete <strong>{{ $member->name }}</strong> ({{ $member->pustanumber }})?</p>
                                            <p class="uk-text-meta">This action cannot be undone.</p>
                                            <div class="uk-flex uk-flex-right uk-margin-top">
                                                <button class="uk-button uk-button-default uk-modal-close uk-margin-small-right">Cancel</button>
                                                <button wire:click="delete({{ $member->id }})" class="uk-button uk-button-danger uk-modal-close" wire:loading.attr="disabled">
                                                    Delete
                                                    <span wire:loading wire:target="delete({{ $member->id }})" class="uk-margin-small-left" uk-spinner="ratio: 0.6"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <!-- Pagination -->
                        <div class="uk-margin-top">
                            {{ $members->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- UIkit CSS/JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.21.9/dist/css/uikit.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.21.9/dist/js/uikit.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.21.9/dist/js/uikit-icons.min.js"></script>
</div>