<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Counts reads of a post. An opened story sends one small request, answered here before
 * Laravel starts by adding the post's id to a file. Once a minute the lines are added up and
 * written to posts.views, so a story going viral never holds up the database.
 *
 * Only the post id is kept, nothing about the reader. public/index.php loads this file
 * by hand, so answer() may not use anything from Laravel.
 */
class ReadCounter
{
    private const LARGEST_FILE_BYTES = 5000000;
    private const FINISHED_AFTER_SECONDS = 60;

    /**
     * Records the read and stops, or returns when this request is something else.
     */
    public static function answer(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
            || ! preg_match('~^/read/([1-9]\d{0,9})$~D', $_SERVER['REQUEST_URI'] ?? '', $match)) {
            return;
        }

        if (self::shouldCount()) {
            $folder = self::folder();
            $file = $folder.'/'.gmdate('YmdHi').'.log';

            @mkdir($folder, 0775, true);

            // A flood of fake reads can skew a number, but it can never fill the disk.
            if ((int) @filesize($file) < self::LARGEST_FILE_BYTES) {
                @file_put_contents($file, $match[1]."\n", FILE_APPEND | LOCK_EX);
            }
        }

        http_response_code(204);
        exit;
    }

    /**
     * Adds finished files to posts.views, and returns how many reads went in.
     */
    public static function flush(): int
    {
        $reads = [];
        $finished = [];

        foreach (glob(self::folder().'/*.log') ?: [] as $file) {
            if (time() - (int) @filemtime($file) <= self::FINISHED_AFTER_SECONDS) {
                continue;
            }

            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $id) {
                $reads[(int) $id] = ($reads[(int) $id] ?? 0) + 1;
            }

            $finished[] = $file;
        }

        // Posts with the same number of new reads go in one statement.
        $postsByCount = [];

        foreach ($reads as $id => $count) {
            $postsByCount[$count][] = $id;
        }

        if ($postsByCount !== []) {
            DB::transaction(function () use ($postsByCount) {
                foreach ($postsByCount as $count => $ids) {
                    foreach (array_chunk($ids, 500) as $chunk) {
                        // Through the query builder, so that a read does not change the post's updated_at.
                        DB::table('posts')->whereIn('id', $chunk)->increment('views', $count);
                    }
                }
            });
        }

        foreach ($finished as $file) {
            @unlink($file);
        }

        return array_sum($reads);
    }

    /**
     * Pages on this site only, and not staff. Readers have no session cookie, because
     * the public pages never set one, so that cookie is how staff are told apart.
     */
    private static function shouldCount(): bool
    {
        if (! in_array($_SERVER['HTTP_SEC_FETCH_SITE'] ?? 'same-origin', ['same-origin', 'none'], true)) {
            return false;
        }

        foreach (array_keys($_COOKIE) as $name) {
            if (str_ends_with((string) $name, '-session') || str_ends_with((string) $name, '_session')) {
                return false;
            }
        }

        return true;
    }

    private static function folder(): string
    {
        return dirname(__DIR__, 2).'/storage/framework/reads';
    }
}
