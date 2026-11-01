<?php

namespace App\Livewire\Frontend;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The comment form and approved comments under a post. It is its own component so that
 * sending a comment does not redraw the post, which would wipe the share buttons.
 */
class PostComments extends Component
{
    private const MAX_PER_VISITOR = 3;
    private const LIMIT_SECONDS = 600;
    private const LIST_SIZE = 50;

    #[Locked]
    public int $postId;

    public string $name = '';
    public string $email = '';
    public string $body = '';

    // Hidden from readers, so anything in it came from a bot.
    public string $website = '';

    public bool $sent = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:80',
            'email' => 'required|email|max:255',
            'body' => 'required|string|min:3|max:1000',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'कृपया आफ्नो नाम लेख्नुहोस्।',
            'name.min' => 'नाम कम्तीमा २ अक्षरको हुनुपर्छ।',
            'name.max' => 'नाम ८० अक्षरभन्दा लामो हुनु हुँदैन।',
            'email.required' => 'कृपया आफ्नो इमेल लेख्नुहोस्।',
            'email.email' => 'इमेल ठेगाना मिलेन।',
            'email.max' => 'इमेल ठेगाना धेरै लामो भयो।',
            'body.required' => 'कृपया आफ्नो प्रतिक्रिया लेख्नुहोस्।',
            'body.min' => 'प्रतिक्रिया कम्तीमा ३ अक्षरको हुनुपर्छ।',
            'body.max' => 'प्रतिक्रिया १००० अक्षरभन्दा लामो हुनु हुँदैन।',
        ];
    }

    public function submit(): void
    {
        $this->name = trim($this->name);
        $this->email = trim($this->email);
        $this->body = trim($this->body);

        $this->validate();

        // A bot sees the same thank-you as a reader, so it has no reason to try again.
        if ($this->website !== '') {
            $this->sent = true;

            return;
        }

        $limitKey = 'post-comment:'.request()->ip();

        if (RateLimiter::tooManyAttempts($limitKey, self::MAX_PER_VISITOR)) {
            $this->addError('body', 'धेरै प्रतिक्रिया पठाइयो। केही मिनेटपछि फेरि प्रयास गर्नुहोस्।');

            return;
        }

        RateLimiter::hit($limitKey, self::LIMIT_SECONDS);

        Post::published()->findOrFail($this->postId)->comments()->create([
            'author_name' => $this->name,
            'author_email' => $this->email,
            'body' => $this->body,
        ]);

        $this->reset('body');
        $this->sent = true;
    }

    public function writeAnother(): void
    {
        $this->sent = false;
    }

    public function render()
    {
        $approved = Comment::approved()->where('post_id', $this->postId);

        return view('livewire.frontend.post-comments', [
            'total' => $approved->count(),
            // Only what the page prints, so a reader's email can never reach the HTML.
            'comments' => $approved->latest()->orderByDesc('id')->take(self::LIST_SIZE)->get(['id', 'author_name', 'body', 'created_at']),
        ]);
    }
}
