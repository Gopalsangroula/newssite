<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\Visitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Comments sent from a post page. The route has no session and acts for nobody, so there is
 * no login for a forged request to borrow, and CSRF tokens add nothing here. Anything sent
 * is held for moderation, and each address is limited to a few comments.
 */
class CommentController extends Controller
{
    // Many Nepali mobile users share one address, so this leaves room for real readers.
    private const MAX_PER_VISITOR = 10;
    private const LIMIT_SECONDS = 600;

    public function store(Request $request, int $post): JsonResponse
    {
        // Always a JSON answer, including for validation errors.
        $request->headers->set('Accept', 'application/json');

        $origin = $request->headers->get('Origin');
        abort_if($origin !== null && parse_url($origin, PHP_URL_HOST) !== $request->getHost(), 403);

        $fields = $request->validate([
            'name' => 'required|string|min:2|max:80',
            'email' => 'required|email|max:255',
            'body' => 'required|string|min:3|max:1000',
        ], [
            'name.required' => 'कृपया आफ्नो नाम लेख्नुहोस्।',
            'name.min' => 'नाम कम्तीमा २ अक्षरको हुनुपर्छ।',
            'name.max' => 'नाम ८० अक्षरभन्दा लामो हुनु हुँदैन।',
            'email.required' => 'कृपया आफ्नो इमेल लेख्नुहोस्।',
            'email.email' => 'इमेल ठेगाना मिलेन।',
            'email.max' => 'इमेल ठेगाना धेरै लामो भयो।',
            'body.required' => 'कृपया आफ्नो प्रतिक्रिया लेख्नुहोस्।',
            'body.min' => 'प्रतिक्रिया कम्तीमा ३ अक्षरको हुनुपर्छ।',
            'body.max' => 'प्रतिक्रिया १००० अक्षरभन्दा लामो हुनु हुँदैन।',
        ]);

        // A bot sees the same thank-you as a reader, so it has no reason to try again.
        if (filled($request->input('website'))) {
            return response()->json(['sent' => true]);
        }

        $limitKey = 'post-comment:'.Visitor::limitKey($request);

        if (RateLimiter::tooManyAttempts($limitKey, self::MAX_PER_VISITOR)) {
            return response()->json([
                'errors' => ['body' => ['धेरै प्रतिक्रिया पठाइयो। केही मिनेटपछि फेरि प्रयास गर्नुहोस्।']],
            ], 429);
        }

        RateLimiter::hit($limitKey, self::LIMIT_SECONDS);

        Post::published()->findOrFail($post)->comments()->create([
            'author_name' => $fields['name'],
            'author_email' => $fields['email'],
            'body' => $fields['body'],
        ]);

        return response()->json(['sent' => true]);
    }
}
