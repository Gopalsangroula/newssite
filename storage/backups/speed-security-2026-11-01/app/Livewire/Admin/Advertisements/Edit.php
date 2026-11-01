<?php

namespace App\Livewire\Admin\Advertisements;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Advertisement;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;

#[Title('Edit Advertisement')]
class Edit extends Component
{
    use WithFileUploads;

    public $adId;

    public $title;
    public $slug;
    public $type;
    public $placement;
    public $content;
    public $image;
    public $existingImage;
    public $target_url;
    public $is_active;
    public $starts_at;
    public $ends_at;

    public function mount($id)
    {
        $ad = Advertisement::findOrFail($id);

        $this->adId = $ad->id;
        $this->title = $ad->title;
        $this->slug = $ad->slug;
        $this->type = $ad->type;
        $this->placement = $ad->placement;
        $this->content = $ad->content;
        $this->existingImage = $ad->image_url;
        $this->target_url = $ad->target_url;
        $this->is_active = $ad->is_active;
        $this->starts_at = optional($ad->starts_at)->format('Y-m-d\TH:i');
        $this->ends_at = optional($ad->ends_at)->format('Y-m-d\TH:i');
    }

    public function updatedTitle($value)
    {
        $this->slug = Str::slug($value);
    }

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:advertisements,slug,' . $this->adId,
            'type' => 'required|in:image,script',
            'placement' => 'nullable|string|max:255',
            'content' => 'nullable|string|required_if:type,script',
            'image' => 'nullable|image|max:2048|required_if:type,image',
            'target_url' => 'nullable|url',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ];
    }

    public function update()
    {
        $this->validate();

        $ad = Advertisement::findOrFail($this->adId);

        if ($this->type === 'image' && $this->image) {
            $imagePath = $this->image->store('ads', 'public');
        } else {
            $imagePath = $ad->image_url;
        }

        $ad->update([
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type,
            'placement' => $this->placement,
            'content' => $this->type === 'script' ? $this->content : null,
            'image_url' => $this->type === 'image' ? $imagePath : null,
            'target_url' => $this->target_url,
            'is_active' => $this->is_active,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
        ]);

        session()->flash('message', 'Advertisement updated successfully.');
        return redirect()->route('admin.advertisements.index');
    }

    public function render()
    {
        return view('livewire.admin.advertisements.edit');
    }
}
