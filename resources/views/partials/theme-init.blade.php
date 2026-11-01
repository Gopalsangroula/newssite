{{-- Runs before the page draws, so a reader who chose dark mode never sees a white flash. --}}
<script>
    try {
        if (localStorage.getItem('libang-theme') === 'dark') {
            document.documentElement.dataset.theme = 'dark';
        }
    } catch (error) {}
</script>
