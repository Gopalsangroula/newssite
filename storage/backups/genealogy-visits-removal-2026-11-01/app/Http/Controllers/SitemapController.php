<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;

class SitemapController extends Controller
{
    /**
     * Every public page search engines should index.
     */
    public function __invoke()
    {
        $urls = [[url('/'), null], [route('frontend.genealogy.index'), null]];

        foreach (Post::published()->where('noindex', false)->latest('updated_at')->get(['slug', 'updated_at']) as $post) {
            $urls[] = [route('frontend.details.show', $post->slug), $post->updated_at];
        }

        foreach (Page::published()->where('noindex', false)->latest('updated_at')->get(['slug', 'updated_at']) as $page) {
            $urls[] = [route('frontend.frontend.pages.show', $page->slug), $page->updated_at];
        }

        foreach (Category::whereNotNull('slug')->get(['slug', 'updated_at']) as $category) {
            $urls[] = [route('frontend.category.show', $category->slug), $category->updated_at];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as [$location, $modified]) {
            $xml .= '  <url><loc>'.e($location).'</loc>';
            $xml .= $modified ? '<lastmod>'.$modified->toAtomString().'</lastmod>' : '';
            $xml .= "</url>\n";
        }

        return response($xml.'</urlset>', 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
