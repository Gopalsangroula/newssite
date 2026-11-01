{{-- Deferred, so they never hold up the page. The integrity values refuse a file that has been changed on the CDN. --}}
<script defer src="https://cdn.jsdelivr.net/npm/uikit@3.23.11/dist/js/uikit.min.js" integrity="sha384-IcAIwepMk8TeppojBmDMdKEsQ8rj3ZCzuatfCE7vBW7FGH4pBNimt0xhGMDkYCmZ" crossorigin="anonymous"></script>
<script defer src="https://cdn.jsdelivr.net/npm/uikit@3.23.11/dist/js/uikit-icons.min.js" integrity="sha384-bEjHICwtRaUsAUdupFlZPIiTHNNYDWC1Bk7D3LrFXWg7+hIpHhj2jMvcrivhhmgv" crossorigin="anonymous"></script>
<script defer src="{{ asset('js/site-tools.js') }}?v={{ filemtime(public_path('js/site-tools.js')) }}"></script>
@stack('scripts')
