<x-layouts.app :title="$post->title">
    <div class="uk-container uk-container-large uk-padding">
        <h1 class="text-3xl font-bold mb-4">{{ $post->title }}</h1>
        <img src="{{ asset('storage/' . $post->cover) }}" alt="{{ $post->title }}" class="w-full h-96 object-cover rounded-lg mb-4">
        <div class="prose dark:prose-invert">
            {!! $post->content !!}
        </div>
    </div>
</x-layouts.app>
