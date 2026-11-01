<?php

namespace App\Livewire\Admin\Advertisements;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Advertisement;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Exception;

class Create extends Component
{
    use WithFileUploads;

    #[Validate('required|string|max:255')]
    public $title;

    #[Validate('required|string|max:255|unique:advertisements,slug')]
    public $slug;

    #[Validate('required|in:image,script')]
    public $type = 'image'; // Default type

    #[Validate('nullable|string|max:255')]
    public $placement;

    #[Validate('nullable|string|required_if:type,script')]
    public $content;

    #[Validate('nullable|image|max:2048|required_if:type,image')] // Max 2MB
    public $image;

    #[Validate('nullable|url')]
    public $target_url;

    #[Validate('boolean')]
    public $is_active = true;

    #[Validate('nullable|date')]
    public $starts_at;

    #[Validate('nullable|date|after_or_equal:starts_at')]
    public $ends_at;

    public $imagePreview = null; // For image preview

    /**
     * Custom validation messages.
     */
    protected $messages = [
        'title.required' => 'The advertisement title is required.',
        'slug.unique' => 'This slug is already in use. Please choose another.',
        'type.required' => 'Please select an advertisement type.',
        'image.required_if' => 'An image is required for image-type advertisements.',
        'content.required_if' => 'Script content is required for script-type advertisements.',
        'target_url.url' => 'The target URL must be a valid URL.',
        'ends_at.after_or_equal' => 'The end date must be on or after the start date.',
    ];

    /**
     * Real-time slug generation from title.
     */
    public function updatedTitle($value)
    {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug;
        $count = 1;

        // Ensure slug is unique
        while (Advertisement::where('slug', $slug)->exists()) {
            $slug = "$baseSlug-$count";
            $count++;
        }

        $this->slug = $slug;
        $this->validateOnly('slug'); // Real-time validation
    }

    /**
     * Real-time image preview.
     */
    public function updatedImage()
    {
        $this->validateOnly('image');
        if ($this->image) {
            $this->imagePreview = $this->image->temporaryUrl();
        } else {
            $this->imagePreview = null;
        }
    }

    /**
     * Save the advertisement.
     */
    public function save()
    {
        $this->validate();

        try {
            $imagePath = null;
            if ($this->type === 'image' && $this->image) {
                $imagePath = $this->image->store('ads', 'public');
            }

            Advertisement::create([
                'title' => $this->title,
                'slug' => $this->slug,
                'type' => $this->type,
                'placement' => $this->placement,
                'content' => $this->type === 'script' ? strip_tags($this->content) : null, // Sanitize script content
                'image_url' => $this->type === 'image' ? $imagePath : null,
                'target_url' => $this->target_url,
                'is_active' => $this->is_active,
                'starts_at' => $this->starts_at ? \Carbon\Carbon::parse($this->starts_at) : null,
                'ends_at' => $this->ends_at ? \Carbon\Carbon::parse($this->ends_at) : null,
            ]);

            $this->resetForm();
            $this->dispatch('advertisementCreated', message: 'Advertisement created successfully.');
            return redirect()->route('admin.advertisements.index');
        } catch (Exception $e) {
            $this->addError('general', 'Failed to create advertisement: ' . $e->getMessage());
        }
    }

    /**
     * Reset form fields after successful save.
     */
    private function resetForm()
    {
        $this->reset([
            'title',
            'slug',
            'type',
            'placement',
            'content',
            'image',
            'target_url',
            'starts_at',
            'ends_at',
            'imagePreview',
        ]);
        $this->type = 'image'; // Reset to default
        $this->is_active = true; // Reset to default
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.admin.advertisements.create');
    }
}