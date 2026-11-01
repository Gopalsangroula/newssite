<?php

use Illuminate\Support\Facades\Route;

// ----------------------
// ADMIN ROUTES
// ----------------------
// Authenticated Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // Settings
    Route::redirect('/settings', '/settings/profile');
    Volt::route('/settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('/settings/password', 'settings.password')->name('settings.password');
    Volt::route('/settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Admin/Editor Routes
    Route::prefix('admin')->middleware('role:super-admin|admin|editor')->group(function () {
        // Posts
        Route::get('/posts', PostIndex::class)->name('admin.posts.index');
        Route::get('/posts/create', PostCreate::class)->name('admin.posts.create');
        Route::get('/posts/{id}/edit', PostEdit::class)->name('admin.posts.edit');
        Route::post('/posts/editor-upload', [PostCreate::class, 'uploadEditorImage'])->name('admin.posts.editor-upload');

        // Categories
        Route::get('/categories', CategoryIndex::class)->name('admin.categories.index');
        Route::get('/categories/create', CategoryCreate::class)->name('admin.categories.create');
        Route::get('/categories/{id}/edit', CategoryEdit::class)->name('admin.categories.edit');

        // Tags
        Route::get('/tags', TagIndex::class)->name('admin.tags.index');
        Route::get('/tags/create', TagCreate::class)->name('admin.tags.create');
        Route::get('/tags/{id}/edit', TagEdit::class)->name('admin.tags.edit');

        // Comments
        Route::get('/comments', CommentIndex::class)->name('admin.comments.index');
        Route::get('/comments/create', CommentCreate::class)->name('admin.comments.create');
        Route::get('/comments/{id}/edit', CommentEdit::class)->name('admin.comments.edit');

        // Media
        Route::get('/media', MediaIndex::class)->name('admin.media.index');
        Route::get('/media/create', MediaCreate::class)->name('admin.media.create');
        Route::get('/media/{id}/edit', MediaEdit::class)->name('admin.media.edit');

        // Media JSON List (for modals/editors)
        Route::get('/media/list', function () {
            try {
                $search = request('search', '');
                $images = Media::when($search, function ($query, $search) {
                    return $query->where('original_name', 'like', "%$search%")
                                 ->orWhere('filename', 'like', "%$search%");
                })
                ->latest()
                ->get()
                ->map(fn($media) => [
                    'url' => $media->url,
                    'date' => $media->created_at->timestamp,
                    'alt' => $media->original_name ?? pathinfo($media->filename, PATHINFO_FILENAME),
                ]);

                return response()->json($images)->header('Cache-Control', 'no-cache, no-store, must-revalidate');
            } catch (\Exception $e) {
                Log::error('admin.media.list error: ' . $e->getMessage(), [
                    'exception' => $e,
                    'search' => request('search', ''),
                ]);
                return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
            }
        })->name('admin.media.list');


    });
    
    // advertiser-Only Routes
        Route::prefix('admin')->middleware('role:advertiser')->group(function () {
           Route::get('/advertisements', AdvertisementIndex::class)->name('admin.advertisements.index');
            Route::get('/advertisements/create', AdvertisementCreate::class)->name('admin.advertisements.create');
            Route::get('/advertisements/{id}/edit', AdvertisementEdit::class)->name('admin.advertisements.edit');
          });
    
            // Admin-Only Routes
        Route::prefix('admin')->middleware(['auth', 'role:admin|super-admin'])->group(function () {
        // Volt routes
            Volt::route('/users', 'admin.users')->name('admin.users');
            Volt::route('/roles', 'admin.roles')->name('admin.roles');
             // Livewire route for register
            Route::get('/register', Register::class)->name('admin.register');

            // Livewire route for PasswordReset
            Route::get('/users/{userId}/password', \App\Livewire\Admin\PasswordReset::class)->name('admin.passwordreset');
                    // Advertisements
            Route::get('/advertisements', AdvertisementIndex::class)->name('admin.advertisements.index');
            Route::get('/advertisements/create', AdvertisementCreate::class)->name('admin.advertisements.create');
            Route::get('/advertisements/{id}/edit', AdvertisementEdit::class)->name('admin.advertisements.edit');
        });

});