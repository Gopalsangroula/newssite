{{-- A public page inside the site layout. Takes $content, the view to show, and that view's data. --}}
<x-layouts.frontend :title="$title ?? null" :meta-description="$metaDescription ?? null" :post="$post ?? null" :page="$page ?? null">
    @include($content)
</x-layouts.frontend>
