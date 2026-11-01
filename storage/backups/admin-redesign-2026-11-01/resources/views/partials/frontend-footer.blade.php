<a href="#" class="ah-scrtop" uk-totop="uk-totop" uk-scroll="uk-scroll"></a>
<div class="footer-nav uk-margin-large-top">
    <div class="uk-container">
        <div class="uk-margin-top-top uk-text-light lokpath-team" uk-grid="uk-grid">
            <div class="uk-width-1-6@s uk-text-center">
                <a href="/" class="uk-text-center footer-logo"><img
                        src="{{ asset('img/libang.jpeg') }}" alt="Appharu Logo" title="Appharu Logo"
                        class="uk-width-2-3"></a>
            </div>
            <div class="uk-width-expand@s">
                <div class="uk-grid-divider uk-child-width-expand@s uk-child-width-1-2 uk-grid-small footer-details"
                    uk-grid>
                    <div>
                        <h4>केत्तकुम लिबाङ पाङ्पत सयङ</h4>
                        <p> केन्द्रीय कार्यालय</p>
                        <h4>विर्तामोड - १, झापा</h4>
                    </div>
                    <div>
                        <p>सम्पर्क</p>
                        <h4> 9851129961</h4>
                    </div>
                    <div>
                        <p>इमेल</p>
                        <h4>libangsayang@gmail.com</h4>
                    </div>
                </div>
                {{-- <hr>
                <div class="uk-navbar-left nav-btn-op">
                    <ul class="uk-navbar-nav uk-light footer-nav-sm">
                        <li><a href="/team">हाम्रो टीम</a></li>
                        <li><a href="/about">हाम्रो बारेमा</a> </li>
                        <li><a href="/policy">गोपनीयता नीति</a></li>
                        <li><a href="/contact">सम्पर्क गर्नुहोस्</a></li>
                        <li><a href="/unicode">विज्ञापन</a></li>
                        <li><a href="/unicode">यूनिकोड</a></li>
                    </ul>
                </div> --}}
            </div>
            <div class="uk-width-1-4@s">
                <div class="social-icon"><a href="https://www.youtube.com/@drishtikonpodcast"
                        class="uk-icon-button uk-margin-small-right" uk-icon="twitter" target="_blank"></a>
                    <a href="https://www.youtube.com/@drishtikonpodcast" class="uk-icon-button  uk-margin-small-right"
                        uk-icon="facebook" target="_blank"></a>
                    <a href="https://www.youtube.com/@drishtikonpodcast" class="uk-icon-button  uk-margin-small-right"
                        uk-icon="instagram" target="_blank"></a>
                    <a href="https://www.youtube.com/@drishtikonpodcast" class="uk-icon-button" uk-icon="youtube"></a>
                </div>
                {{-- <div class="footer-details uk-margin-small-top" style="text-align: start;">
                    <h4>केत्तकुम लिबाङ पाङ्पत सयङ</h4>
                    <p>9813553188</p>
                    <p> libangpangpatasociet@gmail.com</p>
                </div> --}}
            </div>
        </div>

        <div class="uk-margin-remove-top uk-margin-small-bottom uk-text-center" uk-grid="uk-grid">
            <div class="uk-width-1-1@m">
                <hr>
                <div class="credit uk-text-center">
                    &copy;<?php echo date('Y').' '; ?> केत्तकुम लिबाङ पाङ्पत सयङ |
                    Designed & Devevloped by :
                    <a href="https://www.appharu.com/?ref=libang" class="uk-text-primary">Appharu</a>
                </div>
            </div>
        </div>
    </div>
    <!-- container-fluid -->
    <?php //if ( is_active_sidebar( 'skip-ad' ) ) : ?>
    <div id="modal-container" class="uk-flex-top custom-model uk-modal-container" uk-modal="uk-modal">
        <div class="uk-modal-dialog  uk-margin-auto-vertical skip-ads">
            <div class="uk-position-relative">
                <div class="ek-tools-close ah-close ek-skipads"
                    style="position: absolute!important;right: -18px !important; top: -23px !important; left: unset !important;">
                    <span uk-icon="icon: close; ratio: 1.5"></span>
                </div>
            </div>
            <?php //dynamic_sidebar( 'skip-ad' ); ?>

        </div>
    </div>
    <?php //endif; ?>


    <!-- container-fluid -->
    <?php //if ( is_active_sidebar( 'skip-ad' ) ) : ?>
    <div id="modal-container" class="uk-flex-top custom-model uk-modal-container" uk-modal="uk-modal">
        <div class="uk-modal-dialog  uk-margin-auto-vertical skip-ads">
            <div class="uk-position-relative">
                <div class="ek-tools-close ah-close ek-skipads"
                    style="position: absolute!important;right: -18px !important; top: -23px !important; left: unset !important;">
                    <span uk-icon="icon: close; ratio: 1.5"></span>
                </div>
            </div>
            <?php //dynamic_sidebar( 'skip-ad' ); ?>

        </div>
    </div>
    <?php //endif; ?>

    <script type='text/javascript'
        src='https://platform-api.sharethis.com/js/sharethis.js#property=63d1fde6576edb001943b19d&product=sop'
        async='async'></script>
    @livewireScripts

        <script src="https://cdn.jsdelivr.net/npm/uikit@3.21.5/dist/js/uikit.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/uikit@3.21.5/dist/js/uikit-icons.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" defer></script>
      
</div>
</body>

</html>