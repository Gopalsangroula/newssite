<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Media;
use Livewire\Volt\Volt;
use Illuminate\Http\Request;
use App\Livewire\Admin\Register;
use App\Livewire\Admin\PasswordReset;
use App\Livewire\Admin\Posts\Index as PostIndex;
use App\Livewire\Admin\Posts\Create as PostCreate;
use App\Livewire\Admin\Posts\Edit as PostEdit;
use App\Livewire\Admin\Categories\Index as CategoryIndex;
use App\Livewire\Admin\Categories\Create as CategoryCreate;
use App\Livewire\Admin\Categories\Edit as CategoryEdit;
use App\Livewire\Admin\Tags\Index as TagIndex;
use App\Livewire\Admin\Tags\Create as TagCreate;
use App\Livewire\Admin\Tags\Edit as TagEdit;
use App\Livewire\Admin\Comments\Index as CommentIndex;
use App\Livewire\Admin\Comments\Create as CommentCreate;
use App\Livewire\Admin\Comments\Edit as CommentEdit;
use App\Livewire\Admin\Media\Index as MediaIndex;
use App\Livewire\Admin\Media\Create as MediaCreate;
use App\Livewire\Admin\Media\Edit as MediaEdit;
use App\Livewire\Admin\Advertisements\Index as AdvertisementIndex;
use App\Livewire\Admin\Advertisements\Create as AdvertisementCreate;
use App\Livewire\Admin\Advertisements\Edit as AdvertisementEdit;
use App\Livewire\Frontend\CategoryPage;
use App\Livewire\Frontend\SinglePost;
use App\Livewire\Frontend\SinglePage;
use App\Livewire\Frontend\AuthorPage;
use App\Livewire\Frontend\SearchPage;
use App\Livewire\Frontend\TagPage;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ToolsController;

use App\Livewire\Admin\Pages\Index as PageIndex;
use App\Livewire\Admin\Pages\Create as PageCreate;
use App\Livewire\Admin\Pages\Edit as PageEdit;

use App\Livewire\Dashboard;
use App\Livewire\Admin\MetalRates;
use App\Livewire\Admin\PatroEvents;
use App\Livewire\Admin\Rashifals;
use App\Livewire\Admin\SeoOverview;
use App\Livewire\Admin\SeoSettings;

// Load auth routes
require __DIR__ . '/auth.php';

// ----------------------
// FRONTEND ROUTES (Public, Prefixed)
// ----------------------   
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/', function () {return view('welcome');})->name('welcome');
Route::get('/dashboard', [HomeController::class, 'index'])->name('welcome');
Route::redirect('/settings', '/settings/profile');

// Other frontend routes
Route::name('frontend.')->group(function () {
    // Home page
    Route::get('/', [HomeController::class, 'index'])->name('welcome');

    // Single Post page (by slug)
    Route::get('/details/{slug}', SinglePost::class)->name('details.show');

    // Static pages
    Route::get('/pages/{slug}', SinglePage::class)->name('frontend.pages.show');

    // Author Archive
    Route::get('/author/{slug}', AuthorPage::class)->name('author.show');

    // Tag Archive
    Route::get('/tag/{slug}', TagPage::class)->name('tag.show');

    // Search and the reader's saved posts
    Route::get('/search', SearchPage::class)->name('search');
    Route::view('/saved-posts', 'saved-posts')->name('saved');

    // Tools
    Route::prefix('tools')->name('tools.')->controller(ToolsController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/forex', 'forex')->name('forex');
        Route::get('/gold-silver', 'goldSilver')->name('gold');
        Route::get('/weather', 'weather')->name('weather');
        Route::get('/air-quality', 'airQuality')->name('air');
        Route::get('/patro', 'patro')->name('patro');
        Route::get('/rashifal', 'rashifal')->name('rashifal');
        Route::get('/date-converter', 'dateConverter')->name('converter');
    });

    // Category Archive (catch-all slug) — KEEP THIS LAST so it doesn't override other specific routes
    Route::get('/{slug}', CategoryPage::class)->name('category.show');
});

Route::get('/home', [HomeController::class, 'index'])->name('home');
// ----------------------
// BACKEND ROUTES (Authenticated)
// ----------------------
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Volt::route('/settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('/settings/password', 'settings.password')->name('settings.password');
    Volt::route('/settings/appearance', 'settings.appearance')->name('settings.appearance');

    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Page access comes from the permissions in App\Support\PageAccess, set on the Roles page.

        // Media upload and picker: used by every page that has an image field.
        Route::post('/media/upload', function (Request $request) {
            try {
                $request->validate([
                    'media_file' => 'required|image|mimes:jpeg,png,gif,webp|max:10240'
                ]);
                
                $file = $request->file('media_file');
                $filename = time() . '_' . \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('media/' . now()->format('Y/m'), $filename, 'public');
                
                $media = Media::create([
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'url' => Storage::url($path),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'user_id' => auth()->id()
                ]);
                
                return response()->json(['success' => true, 'media' => $media]);
                
            } catch (\Exception $e) {
                Log::error('Media upload error: ' . $e->getMessage());
                return response()->json(['error' => $e->getMessage()], 500);
            }
        })->middleware('permission:access media|access posts|access pages|access advertisements')->name('media.upload');

        Route::get('/gold-silver', MetalRates::class)->middleware('permission:access gold silver')->name('metal-rates');
        Route::get('/patro-events', PatroEvents::class)->middleware('permission:access patro')->name('patro-events');
        Route::get('/rashifal', Rashifals::class)->middleware('permission:access rashifal')->name('rashifal');
        Route::get('/seo', SeoOverview::class)->middleware('permission:access seo')->name('seo');
        Route::get('/seo/settings', SeoSettings::class)->middleware('permission:access seo settings')->name('seo.settings');

        Route::middleware('permission:access posts')->group(function () {
            Route::get('/posts', PostIndex::class)->name('posts.index');
            Route::get('/posts/create', PostCreate::class)->name('posts.create');
            Route::get('/posts/{id}/edit', PostEdit::class)->name('posts.edit');
            Route::post('/posts/editor-upload', [PostCreate::class, 'uploadEditorImage'])->name('posts.editor-upload');
        });

        Route::middleware('permission:access pages')->group(function () {
            Route::get('/pages', PageIndex::class)->name('pages.index');
            Route::get('/pages/create', PageCreate::class)->name('pages.create');
            Route::get('/pages/{id}/edit', PageEdit::class)->name('pages.edit');
        });

        Route::middleware('permission:access categories')->group(function () {
            Route::get('/categories', CategoryIndex::class)->name('categories.index');
            Route::get('/categories/create', CategoryCreate::class)->name('categories.create');
            Route::get('/categories/{id}/edit', CategoryEdit::class)->name('categories.edit');
        });

        Route::middleware('permission:access tags')->group(function () {
            Route::get('/tags', TagIndex::class)->name('tags.index');
            Route::get('/tags/create', TagCreate::class)->name('tags.create');
            Route::get('/tags/{id}/edit', TagEdit::class)->name('tags.edit');
        });

        Route::middleware('permission:access comments')->group(function () {
            Route::get('/comments', CommentIndex::class)->name('comments.index');
            Route::get('/comments/create', CommentCreate::class)->name('comments.create');
            Route::get('/comments/{id}/edit', CommentEdit::class)->name('comments.edit');
        });

        Route::middleware('permission:access media')->group(function () {
            Route::get('/media', MediaIndex::class)->name('media.index');
            Route::get('/media/create', MediaCreate::class)->name('media.create');
            Route::get('/media/{id}/edit', MediaEdit::class)->name('media.edit');
        });

        Route::middleware('permission:access media|access posts|access pages|access advertisements')->group(function () {
            Route::get('/media/list', function (Request $request) {
                try {
                    $search = $request->get('search', '');
                    $images = Media::when($search, function ($query, $search) {
                        return $query->where('original_name', 'like', "%$search%")
                                    ->orWhere('filename', 'like', "%$search%");
                    })
                    ->latest()
                    ->get()
                    ->map(function ($media) {
                        return [
                            'url' => $media->url,
                            'date' => $media->created_at->timestamp,
                            'alt' => $media->original_name ?? pathinfo($media->filename, PATHINFO_FILENAME),
                            'original_name' => $media->original_name,
                        ];
                    });

                    return response()->json($images)->header('Cache-Control', 'no-cache, no-store, must-revalidate');
                } catch (\Exception $e) {
                    Log::error('admin.media.list error: ' . $e->getMessage(), [
                        'exception' => $e,
                        'search' => $request->get('search', ''),
                    ]);
                    return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
                }
            })->name('media.list');
        });

        Route::middleware('permission:access advertisements')->group(function () {
            Route::get('/advertisements', AdvertisementIndex::class)->name('advertisements.index');
            Route::get('/advertisements/create', AdvertisementCreate::class)->name('advertisements.create');
            Route::get('/advertisements/{id}/edit', AdvertisementEdit::class)->name('advertisements.edit');
        });

        Route::middleware('permission:access users')->group(function () {
            Volt::route('/users', 'admin.users')->name('users');
            Route::get('/register', Register::class)->name('register');
            Route::get('/users/{userId}/password', PasswordReset::class)->name('passwordreset');
        });

        Volt::route('/roles', 'admin.roles')->middleware('permission:access roles')->name('roles');
    });
});

// Storage Route (for media)
Route::get('storage/{path}', function ($path) { return Storage::disk('local')->response($path); })->where('path', '.*')->name('storage.local');

// Health check
Route::get('/up', function () {
    return 'OK';
});

Route::fallback(function () {
    abort(404);
});