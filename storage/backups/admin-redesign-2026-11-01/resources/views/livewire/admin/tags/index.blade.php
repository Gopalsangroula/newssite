<div>
    <h1 class="text-2xl font-bold mb-4">Tags</h1>

    @if (session()->has('message'))
        <div class="bg-green-200 p-2 mb-4 rounded">{{ session('message') }}</div>
    @endif

    <a href="{{ route('admin.tags.create') }}" class="btn btn-primary mb-4">+ New Tag</a>

    <table class="table-auto w-full border">
        <thead>
            <tr>
                <th class="border px-4 py-2">Name</th>
                <th class="border px-4 py-2">Slug</th>
                <th class="border px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tags as $tag)
            <tr>
                <td class="border px-4 py-2">{{ $tag->name }}</td>
                <td class="border px-4 py-2">{{ $tag->slug }}</td>
                <td class="border px-4 py-2">
                    <a href="{{ route('admin.tags.edit', $tag->id) }}" class="text-blue-600 mr-2">Edit</a>
                    <button wire:click="delete({{ $tag->id }})" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()" class="text-red-600">Delete</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
