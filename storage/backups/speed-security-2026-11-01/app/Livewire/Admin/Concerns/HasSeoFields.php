<?php

namespace App\Livewire\Admin\Concerns;

/**
 * The SEO fields shared by the post and page forms.
 */
trait HasSeoFields
{
    public $seo_title;
    public $seo_description;
    public $focus_keyword;
    public $og_image;
    public $noindex = false;

    protected function seoRules(): array
    {
        return [
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'focus_keyword' => 'nullable|string|max:255',
            'og_image' => 'nullable|string|max:255',
            'noindex' => 'boolean',
        ];
    }

    protected function fillSeo($model): void
    {
        $this->seo_title = $model->seo_title;
        $this->seo_description = $model->seo_description;
        $this->focus_keyword = $model->focus_keyword;
        $this->og_image = $model->og_image;
        $this->noindex = (bool) $model->noindex;
    }

    protected function seoData(): array
    {
        return [
            'seo_title' => $this->seo_title ?: null,
            'seo_description' => $this->seo_description ?: null,
            'focus_keyword' => $this->focus_keyword ?: null,
            'og_image' => $this->og_image ?: null,
            'noindex' => (bool) $this->noindex,
        ];
    }
}
