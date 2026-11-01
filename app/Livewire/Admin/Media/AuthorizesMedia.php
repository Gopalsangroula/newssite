<?php

namespace App\Livewire\Admin\Media;

use Illuminate\Support\Facades\Log;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

trait AuthorizesMedia
{
    protected function canEditMedia(): bool
    {
        try {
            return (bool) auth()->user()?->hasPermissionTo('edit content', 'web');
        } catch (PermissionDoesNotExist $e) {
            Log::error('Permission edit content does not exist for web guard', [
                'user_id' => auth()->id(),
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function canDeleteMedia(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        try {
            return $user->hasPermissionTo('delete media', 'web');
        } catch (PermissionDoesNotExist $e) {
            // 'delete media' has never been seeded, so the check above can only throw.
            // Until it exists, 'delete content' decides: it is held by the same roles
            // that can reach the media pages. Seeding 'delete media' takes over from this.
            return $user->checkPermissionTo('delete content', 'web');
        }
    }
}
