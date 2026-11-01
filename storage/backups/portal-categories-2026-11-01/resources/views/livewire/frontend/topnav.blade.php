<div class="site-wrap site-top__row">
    <p class="site-date">
        <span>{{ \App\Support\NepaliDate::full(now()) }}</span>
        <span class="site-date__english">{{ now()->format('M j, Y') }}</span>
    </p>

    <ul class="site-contact">
        <li><a href="/pages/contact"><span uk-icon="icon: location; ratio: 0.8"></span>Arjundhara-3, Birtamode Jhapa</a></li>
        <li><a href="tel:+9779851129961"><span uk-icon="icon: phone; ratio: 0.8"></span>+977 985-1129961</a></li>
        <li><a href="mailto:support@libang.com.np"><span uk-icon="icon: mail; ratio: 0.8"></span>support@libang.com.np</a></li>
    </ul>

    <ul class="site-social">
        <li><a href="https://x.com/libang" target="_blank" rel="noopener" uk-icon="icon: twitter; ratio: 0.8" aria-label="X"></a></li>
        <li><a href="https://www.facebook.com/libang/" target="_blank" rel="noopener" uk-icon="icon: facebook; ratio: 0.8" aria-label="Facebook"></a></li>
        <li><a href="https://www.instagram.com/libang" target="_blank" rel="noopener" uk-icon="icon: instagram; ratio: 0.8" aria-label="Instagram"></a></li>
        <li><a href="https://www.youtube.com/user/@libang" target="_blank" rel="noopener" uk-icon="icon: youtube; ratio: 0.8" aria-label="YouTube"></a></li>
    </ul>
</div>
