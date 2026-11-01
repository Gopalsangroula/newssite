<div>
    <h1 class="text-2xl font-bold mb-4">Edit Tag</h1>

    <form wire:submit.prevent="update">
        <div class="mb-4">
            <label>Name</label>
            <input type="text" wire:model="name" class="border rounded p-2 w-full" />
            @error('name') <span class="text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label>Slug</label>
            <input type="text" wire:model="slug" class="border rounded p-2 w-full" />
            @error('slug') <span class="text-red-600">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn btn-primary uk-button uk-button-default">Update</button>
        <a href="{{ route('admin.tags.index') }}" class="btn btn-secondary ml-2 uk-button uk-button-default">Cancel</a>
    </form>
</div>
