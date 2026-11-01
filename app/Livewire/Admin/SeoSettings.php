<?php

namespace App\Livewire\Admin;

use App\Models\SiteSetting;
use App\Support\Seo;
use Illuminate\Support\Arr;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('SEO Settings')]
class SeoSettings extends Component
{
    public $settings = [];

    public function mount()
    {
        $this->settings = Arr::only(Seo::site(), array_keys(Seo::defaults()));
    }

    protected function rules()
    {
        return [
            'settings.site_name' => 'required|string|max:255',
            'settings.title_suffix' => 'nullable|string|max:60',
            'settings.home_title' => 'required|string|max:255',
            'settings.description' => 'required|string|max:500',
            'settings.keywords' => 'nullable|string|max:1000',
            'settings.share_image' => 'nullable|string|max:255',
            'settings.facebook_app_id' => 'nullable|string|max:40',
            'settings.facebook_page_id' => 'nullable|string|max:40',
            'settings.twitter_handle' => 'nullable|string|max:40',
            'settings.google_verification' => 'nullable|string|max:255',
            'settings.bing_verification' => 'nullable|string|max:255',
        ];
    }

    public function save()
    {
        $this->validate();

        foreach (array_keys(Seo::defaults()) as $key) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => trim((string) ($this->settings[$key] ?? ''))]);
        }

        session()->flash('message', 'SEO settings saved.');
    }

    public function render()
    {
        return view('livewire.admin.seo.settings', [
            'siteUrl' => url('/'),
        ]);
    }
}
