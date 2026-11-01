<div>
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">SEO Settings</h1>
            <p class="ah-page-sub">Defaults for the whole site. Each post can override these in its SEO panel.</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('admin.seo') }}" class="uk-button uk-button-default">SEO Overview</a>
            <button type="button" wire:click="save" wire:loading.attr="disabled" class="uk-button uk-button-primary">Save settings</button>
        </div>
    </div>

    @include('partials.editor-alerts')

    <div class="ah-card-grid">
        <div class="ah-card ah-form-card">
            <div class="ah-card-head">
                <h2 class="ah-card-title">Site and home page</h2>
            </div>
            <div class="ah-card-body">
                <div class="ah-form-grid ah-form-grid--single">
                    <div class="ah-field">
                        <label class="uk-form-label" for="site_name">Site name</label>
                        <input id="site_name" type="text" wire:model="settings.site_name" class="uk-input">
                    </div>
                    <div class="ah-field">
                        <label class="uk-form-label" for="title_suffix">Title ending</label>
                        <input id="title_suffix" type="text" wire:model="settings.title_suffix" class="uk-input">
                        <div class="ah-field-hint">Added after each post title in search results.</div>
                    </div>
                    <div class="ah-field">
                        <label class="uk-form-label" for="home_title">Home page title</label>
                        <input id="home_title" type="text" wire:model="settings.home_title" class="uk-input">
                    </div>
                    <div class="ah-field">
                        <label class="uk-form-label" for="description">Default description</label>
                        <textarea id="description" wire:model="settings.description" class="uk-textarea" rows="4"></textarea>
                    </div>
                    <div class="ah-field">
                        <label class="uk-form-label" for="keywords">Home page keywords</label>
                        <textarea id="keywords" wire:model="settings.keywords" class="uk-textarea" rows="3"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="ah-card ah-form-card">
                <div class="ah-card-head">
                    <h2 class="ah-card-title">Facebook and social</h2>
                </div>
                <div class="ah-card-body">
                    <div class="ah-form-grid ah-form-grid--single">
                        <div class="ah-field" x-data>
                            <label class="uk-form-label" for="share_image">Default share image</label>
                            <div class="ah-seo-image-field">
                                <input id="share_image" type="text" wire:model="settings.share_image" class="uk-input">
                                <button type="button" class="uk-button uk-button-default" x-on:click="openImagePicker({ onSelect: ([image]) => $wire.set('settings.share_image', image.url, false) })">Choose</button>
                            </div>
                            <div class="ah-field-hint">Used when a post has no image. Best size: 1200 × 630.</div>
                        </div>
                        <div class="ah-field">
                            <label class="uk-form-label" for="facebook_app_id">Facebook App ID</label>
                            <input id="facebook_app_id" type="text" wire:model="settings.facebook_app_id" class="uk-input">
                        </div>
                        <div class="ah-field">
                            <label class="uk-form-label" for="facebook_page_id">Facebook Page ID</label>
                            <input id="facebook_page_id" type="text" wire:model="settings.facebook_page_id" class="uk-input">
                        </div>
                        <div class="ah-field">
                            <label class="uk-form-label" for="twitter_handle">X (Twitter) handle</label>
                            <input id="twitter_handle" type="text" wire:model="settings.twitter_handle" class="uk-input">
                        </div>
                    </div>
                </div>
            </div>

            <div class="ah-card ah-form-card">
                <div class="ah-card-head">
                    <h2 class="ah-card-title">Search engines</h2>
                </div>
                <div class="ah-card-body">
                    <div class="ah-form-grid ah-form-grid--single">
                        <div class="ah-field">
                            <label class="uk-form-label" for="google_verification">Google verification code</label>
                            <input id="google_verification" type="text" wire:model="settings.google_verification" class="uk-input">
                            <div class="ah-field-hint">The content value from Google Search Console's HTML tag.</div>
                        </div>
                        <div class="ah-field">
                            <label class="uk-form-label" for="bing_verification">Bing verification code</label>
                            <input id="bing_verification" type="text" wire:model="settings.bing_verification" class="uk-input">
                        </div>
                    </div>
                </div>
            </div>

            <div class="ah-card ah-form-card">
                <div class="ah-card-head">
                    <h2 class="ah-card-title">Tools</h2>
                </div>
                <div class="ah-card-body">
                    <ul class="ah-seo-tools">
                        <li><a href="{{ route('sitemap') }}" target="_blank" rel="noopener">Sitemap</a></li>
                        <li><a href="{{ url('/robots.txt') }}" target="_blank" rel="noopener">robots.txt</a></li>
                        <li><a href="https://search.google.com/search-console" target="_blank" rel="noopener">Google Search Console</a></li>
                        <li><a href="https://developers.facebook.com/tools/debug/?q={{ urlencode($siteUrl) }}" target="_blank" rel="noopener">Facebook Sharing Debugger</a></li>
                        <li><a href="https://search.google.com/test/rich-results?url={{ urlencode($siteUrl) }}" target="_blank" rel="noopener">Google Rich Results Test</a></li>
                        <li><a href="https://pagespeed.web.dev/analysis?url={{ urlencode($siteUrl) }}" target="_blank" rel="noopener">PageSpeed Insights</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @include('partials.uploads')
</div>
