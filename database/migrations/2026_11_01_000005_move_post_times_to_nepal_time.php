<?php

use Carbon\CarbonInterface;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MINUTES = 345;

    /**
     * Until the app moved to Nepal time, publishing stamped a post with the UTC clock, so those
     * times now read 5 hours 45 minutes early. A scheduled time has no seconds and was typed in
     * Nepal time, and a time that would land in the future was never UTC. Both stay as they are.
     */
    public function up(): void
    {
        $this->move(self::MINUTES, now()->subMinutes(self::MINUTES));
    }

    public function down(): void
    {
        $this->move(-self::MINUTES, now());
    }

    private function move(int $minutes, CarbonInterface $latest): void
    {
        $posts = DB::table('posts')->where('published_at', '<=', $latest)->get(['id', 'published_at']);

        foreach ($posts as $post) {
            $time = Carbon::parse($post->published_at);

            if ($time->second !== 0) {
                DB::table('posts')->where('id', $post->id)->update(['published_at' => $time->addMinutes($minutes)]);
            }
        }
    }
};
