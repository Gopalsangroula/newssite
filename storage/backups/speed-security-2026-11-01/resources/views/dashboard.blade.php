<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <!-- Stats Card 1 -->
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800 p-6">
                <div class="flex flex-col h-full justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Total Posts</h3>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">0</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage your content</p>
                </div>
            </div>
            
            <!-- Stats Card 2 -->
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800 p-6">
                <div class="flex flex-col h-full justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Categories</h3>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">0</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Organize content</p>
                </div>
            </div>
            
            <!-- Stats Card 3 -->
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800 p-6">
                <div class="flex flex-col h-full justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Users</h3>
                    <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">0</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage users</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions Section -->
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800 p-6">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('admin.posts.create') }}" class="block p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <h4 class="font-semibold text-gray-900 dark:text-white">Create Post</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Add new content</p>
                </a>
                
                <a href="{{ route('admin.categories.create') }}" class="block p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <h4 class="font-semibold text-gray-900 dark:text-white">Add Category</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Create new category</p>
                </a>
                
                <a href="{{ route('admin.media.create') }}" class="block p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <h4 class="font-semibold text-gray-900 dark:text-white">Upload Media</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Add images/files</p>
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>