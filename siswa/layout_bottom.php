</div>
</div>

<!-- Bootstrap & JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!-- Dynamic JS for pages -->
<?= $extra_js ?? '' ?>
<script>
    // 1. Force Clear Cache / No-Cache logic for dynamic elements
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // 2. Anti-Inspect Element & Security
    document.addEventListener('contextmenu', event => event.preventDefault()); // Protect Right Click

    document.onkeydown = function(e) {
        // F12
        if (e.keyCode == 123) return false;
        // Ctrl+Shift+I (Inspect)
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) return false;
        // Ctrl+Shift+C (Inspect Element)
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) return false;
        // Ctrl+Shift+J (Console)
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) return false;
        // Ctrl+U (View Source)
        if (e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) return false;
        // Ctrl+S (Save Page)
        if (e.ctrlKey && e.keyCode == 'S'.charCodeAt(0)) return false;
    };

    $(document).ready(function() {
        $('#sidebarCollapse, #sidebarOverlay').on('click', function() {
            $('#sidebar, #sidebarOverlay').toggleClass('active');
        });

        // Close sidebar on link click (mobile)
        document.querySelectorAll('.sub-item .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 992) {
                    document.getElementById('sidebar').classList.remove('active');
                    document.getElementById('sidebarOverlay').classList.remove('active');
                }
            });
        });
    });
</script>
</body>

</html>