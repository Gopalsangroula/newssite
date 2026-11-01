<section class="uk-section uk-section-default">
    <div class="uk-container uk-container-small">
        
        <!-- Header -->
        <div class="uk-text-center uk-margin-medium-bottom">
            <span class="uk-label uk-label-primary">Official Announcement</span>
            <h2 class="uk-heading-small uk-margin-small-top">अध्यक्षको सन्देश (Chairman's Message)</h2>
            <hr class="uk-divider-small uk-text-center">
        </div>

        <div class="uk-grid-medium uk-flex-middle" uk-grid>
            <!-- Avatar & Profile Block -->
            <div class="uk-width-1-3@s uk-text-center">
                <div class="uk-inline-clip uk-transition-toggle" tabindex="0">
                    <img class="uk-border-rounded uk-box-shadow-medium" src="{{ $pagemessage->cover_image_url }}" width="250" height="300" alt="Chairman">
                </div>
                <h4 class="uk-margin-small-top uk-margin-remove-bottom"><a href="/pages/chairman-massage">{{ $pagemessage?->title }}</a></h4>
                <span class="uk-text-meta">Executive Chairperson</span>
            </div>

            <!-- Message Column -->
            <div class="uk-width-2-3@s">
                <blockquote class="uk-text-large uk-margin-remove-top">
                    <p class="uk-margin-small-bottom">पुर्खाको इतिहास संरक्षण गरौँ, वर्तमानलाई संगठित गरौँ र भावी पुस्ताको भविष्य निर्माण गरौँ</p>
                    
                </blockquote>
                <p style="font-size: 1.1rem;"> {!! Str::limit(strip_tags($pagemessage?->content), 480) !!}</p>
                   <p uk-margin class="uk-text-center">
                        <a class="uk-button uk-button-default ah-read-more" href="/pages/chairman-massage"> पूरा पढ्नुहोस् </a>
                    </p>

            </div>
        </div>

    </div>
</section>