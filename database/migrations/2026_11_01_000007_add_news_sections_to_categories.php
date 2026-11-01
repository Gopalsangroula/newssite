<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The news sections, in menu order. All of them sit under the "news" category.
     */
    private const SECTIONS = [
        'samasamayik' => 'समसामयिक',
        'samaj' => 'समाज',
        'artha-bikas' => 'अर्थ/विकास',
        'bishesh' => 'विशेष',
        'drishtikon' => 'दृष्टिकोण',
        'baatchit' => 'बातचित',
        'kala' => 'कला',
        'khelkud' => 'खेलकुद',
    ];

    public function up(): void
    {
        $now = now();

        if (DB::table('categories')->where('slug', 'news')->doesntExist()) {
            DB::table('categories')->insert(['name' => 'समाचार', 'slug' => 'news', 'created_at' => $now, 'updated_at' => $now]);
        }

        DB::table('categories')->where('slug', 'news')->where('name', 'Samachar')->update(['name' => 'समाचार']);

        $newsId = DB::table('categories')->where('slug', 'news')->value('id');

        foreach (self::SECTIONS as $slug => $name) {
            if (DB::table('categories')->where('slug', $slug)->doesntExist()) {
                DB::table('categories')->insert([
                    'name' => $name,
                    'slug' => $slug,
                    'parent_id' => $newsId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Only sections that nothing has been filed in are removed.
     */
    public function down(): void
    {
        DB::table('categories')
            ->whereIn('slug', array_keys(self::SECTIONS))
            ->whereNotIn('id', DB::table('posts')->select('category_id'))
            ->delete();
    }
};
