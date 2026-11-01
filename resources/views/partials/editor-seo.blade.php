{{-- SEO panel for the post and page forms. Expects $seoPath (the URL prefix), $seoUsesSubTitle and $seoPublicUrl. --}}
@php
    $seoSite = \App\Support\Seo::site();
    $seoConfig = [
        'suffix' => $seoSite['title_suffix'],
        'domain' => parse_url(url('/'), PHP_URL_HOST),
        'path' => $seoPath,
        'usesSubTitle' => $seoUsesSubTitle,
        'fallbackImage' => \App\Support\Seo::absoluteUrl($seoSite['share_image']),
        'titleMin' => \App\Support\Seo::TITLE_MIN,
        'titleMax' => \App\Support\Seo::TITLE_MAX,
        'descriptionMin' => \App\Support\Seo::DESCRIPTION_MIN,
        'descriptionMax' => \App\Support\Seo::DESCRIPTION_MAX,
        'minWords' => \App\Support\Seo::CONTENT_MIN_WORDS,
    ];
@endphp

<div class="ah-seo" id="seo-panel" wire:ignore>
    <script>
        // The same checks as App\Support\Seo::audit(), run live while writing.
        window.ahSeoPanel = (config) => ({
            config,
            body: '',
            firstParagraph: '',
            cover: null,

            init() {
                this.readPage();
                setInterval(() => this.readPage(), 1500);
            },

            readPage() {
                const content = new DOMParser().parseFromString(document.getElementById('editor').value, 'text/html');
                this.body = content.body.textContent.replace(/\s+/g, ' ').trim();
                this.firstParagraph = content.querySelector('p')?.textContent.trim() || this.body;
                this.cover = document.querySelector('.ah-cover img')?.src || null;
            },

            get keyword() {
                return (this.$wire.focus_keyword || '').trim().toLowerCase();
            },
            get headline() {
                return (this.$wire.seo_title || this.$wire.title || 'Add title').trim();
            },
            get searchTitle() {
                return this.$wire.seo_title ? this.headline : `${this.headline} | ${this.config.suffix}`;
            },
            get customDescription() {
                return (this.$wire.seo_description || '').trim();
            },
            get description() {
                const subTitle = this.config.usesSubTitle ? this.$wire.sub_title : '';
                const text = this.customDescription || subTitle || this.body;
                return text.length > this.config.descriptionMax ? `${text.slice(0, this.config.descriptionMax)}...` : text;
            },
            get image() {
                return this.$wire.og_image || this.cover || this.config.fallbackImage;
            },
            get pageUrl() {
                return `${this.config.domain}${this.config.path}${this.$wire.slug || 'your-post'}`;
            },
            get checks() {
                const has = (text) => this.keyword !== '' && text.toLowerCase().includes(this.keyword);
                const titleLength = this.searchTitle.length;
                const descriptionLength = this.customDescription.length;
                const words = this.body.split(' ').filter(Boolean).length;

                return [
                    { label: `Title is ${this.config.titleMin} to ${this.config.titleMax} characters`, pass: titleLength >= this.config.titleMin && titleLength <= this.config.titleMax },
                    { label: `Meta description is ${this.config.descriptionMin} to ${this.config.descriptionMax} characters`, pass: descriptionLength >= this.config.descriptionMin && descriptionLength <= this.config.descriptionMax },
                    { label: 'Has a featured or social image', pass: Boolean(this.$wire.og_image || this.cover) },
                    { label: 'Focus keyword is set', pass: this.keyword !== '' },
                    { label: 'Keyword is in the title', pass: has(this.searchTitle) },
                    { label: 'Keyword is in the meta description', pass: has(this.customDescription) },
                    { label: 'Keyword is in the first paragraph', pass: has(this.firstParagraph) },
                    { label: `Content has at least ${this.config.minWords} words`, pass: words >= this.config.minWords },
                ];
            },
            get score() {
                return Math.round(this.checks.filter((check) => check.pass).length / this.checks.length * 100);
            },
            get scoreClass() {
                if (this.score >= 80) {
                    return 'ah-badge--success';
                }
                return this.score >= 50 ? 'ah-badge--warning' : 'ah-badge--danger';
            },

            pickImage() {
                window.openImagePicker({
                    onSelect: ([image]) => {
                        this.$wire.og_image = image.url;
                    },
                });
            },
        });
    </script>

    <details class="ah-panel" open x-data="ahSeoPanel(@js($seoConfig))">
        <summary>
            SEO
            <span class="ah-badge" :class="scoreClass" x-text="`${score}%`"></span>
        </summary>

        <div class="ah-seo-body">
            <div>
                <label class="ah-field-label" for="focus_keyword">Focus keyword</label>
                <input type="text" id="focus_keyword" wire:model="focus_keyword" class="uk-input" placeholder="The main thing people would search for">

                <div class="ah-seo-label">
                    <label class="ah-field-label" for="seo_title">SEO title</label>
                    <span class="ah-seo-count" :class="{ 'is-over': searchTitle.length > config.titleMax }" x-text="`${searchTitle.length} / ${config.titleMax}`"></span>
                </div>
                <input type="text" id="seo_title" wire:model="seo_title" class="uk-input" maxlength="255" :placeholder="searchTitle">

                <div class="ah-seo-label">
                    <label class="ah-field-label" for="seo_description">Meta description</label>
                    <span class="ah-seo-count" :class="{ 'is-over': customDescription.length > config.descriptionMax }" x-text="`${customDescription.length} / ${config.descriptionMax}`"></span>
                </div>
                <textarea id="seo_description" wire:model="seo_description" class="uk-textarea" rows="3" maxlength="500" placeholder="A short summary that makes people want to click"></textarea>

                <span class="ah-field-label">Social image</span>
                <div class="ah-seo-image">
                    <button type="button" class="uk-button uk-button-default uk-button-small" x-on:click="pickImage()">Choose image</button>
                    <button type="button" class="ah-panel-danger" x-show="$wire.og_image" x-on:click="$wire.og_image = null">Remove</button>
                </div>
                <p class="ah-panel-hint">Uses the featured image if empty. Best size: 1200 × 630.</p>

                <label class="ah-check"><input type="checkbox" class="uk-checkbox" wire:model="noindex"> Hide from search engines</label>
            </div>

            <div>
                <span class="ah-field-label">Google preview</span>
                <div class="ah-serp">
                    <div class="ah-serp-url" x-text="pageUrl"></div>
                    <div class="ah-serp-title" x-text="searchTitle"></div>
                    <div class="ah-serp-text" x-text="description"></div>
                </div>

                <span class="ah-field-label">Facebook preview</span>
                <div class="ah-fb-card">
                    <img :src="image" alt="">
                    <div class="ah-fb-text">
                        <div class="ah-fb-domain" x-text="config.domain"></div>
                        <div class="ah-fb-title" x-text="headline"></div>
                        <div class="ah-fb-description" x-text="description"></div>
                    </div>
                </div>

                @if ($seoPublicUrl)
                    <p class="ah-panel-hint">
                        <a href="https://developers.facebook.com/tools/debug/?q={{ urlencode($seoPublicUrl) }}" target="_blank" rel="noopener">Test on Facebook</a>.
                        After changing a published post, click Scrape Again there, because Facebook keeps its old copy.
                    </p>
                @endif
            </div>
        </div>

        <ul class="ah-seo-checks">
            <template x-for="check in checks" :key="check.label">
                <li :class="{ 'is-pass': check.pass }" x-text="check.label"></li>
            </template>
        </ul>
    </details>
</div>
