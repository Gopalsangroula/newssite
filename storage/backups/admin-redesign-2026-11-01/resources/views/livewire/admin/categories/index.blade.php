<div>
    <h2 class="uk-heading-line"><span>Categories</span></h2>

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

    <a href="{{ route('admin.categories.create') }}" class="uk-button uk-button-primary uk-margin-small-bottom">
        + New Category
    </a>

    <table class="uk-table uk-table-divider uk-table-striped uk-table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Parent</th>
                <th width="150">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categories as $cat)
                <tr>
                    <td>{{ $cat->name }}</td>
                    <td>{{ $cat->slug }}</td>
                    <td>{{ $cat->parent?->name ?? '-' }}</td>
                    <td>
                        <div class="uk-flex">
                            <a href="{{ route('admin.categories.edit', $cat->id) }}"
                               class="uk-button uk-button-small uk-button-default">
                                Edit
                            </a>
                            <button
                                wire:click="delete({{ $cat->id }})"
                                onclick="return confirm('Delete this category?')"
                                class="uk-button uk-button-small uk-button-danger">
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No categories found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $categories->links() }}
    </div>
</div>
