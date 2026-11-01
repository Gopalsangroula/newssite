<!-- Main view for listing and searching genealogy records -->
<div>
    <!-- Page Header -->
    <h2 class="uk-heading-small uk-text-center uk-margin-medium-bottom">Genealogy Manager</h2>

    <!-- Search Bar -->
    <div class="uk-margin-bottom">
        <div class="uk-form-controls">
            <input wire:model.live.debounce.500ms="search" class="uk-input uk-width-1-2@s uk-align-center" type="text" placeholder="Search by name, pusta number, mother name, or wife name...">
        </div>
    </div>

    <!-- Members Table -->
    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-divider uk-table-middle uk-table-responsive">
            <thead>
                <tr>
                    <th>Pusta Number</th>
                    <th>Name</th>
                    <th>Sex</th>
                    <th>Date of Birth</th>
                    <th>Mother Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                    <tr>
                        <td>{{ $member->pustanumber }}</td>
                        <td>{{ $member->name }}</td>
                        <td>{{ ucfirst($member->sex) }}</td>
                        <td>{{ $member->date_of_birth ? $member->date_of_birth->format('Y-m-d') : '-' }}</td>
                        <td>{{ $member->mother_name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.genealogys.update', $member->id) }}" class="uk-button uk-button-primary uk-button-small">Edit</a>
                            <a href="/" class="uk-button uk-button-danger uk-button-small">Delete</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="uk-text-center">No members found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="uk-margin-top">
        {{ $members->links() }}
    </div>

    <!-- Success/Error Messages -->
    @if (session('message'))
        <div class="uk-alert-success" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p>{{ session('message') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="uk-alert-danger" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- Create Button -->
    <div class="uk-margin-top uk-text-center">
        <a href="{{ route('admin.genealogys.create') }}" class="uk-button uk-button-primary">Add New Member</a>
    </div>
</div>