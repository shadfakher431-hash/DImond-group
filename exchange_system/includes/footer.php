        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo __('app_name'); ?>. <?php echo __('copyright'); ?></p>
            <p><?php echo __('developed_by'); ?> Diamond Group</p>
        </div>
    </footer>

    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuToggle')?.addEventListener('click', function() {
            document.getElementById('navbarMenu')?.classList.toggle('active');
        });

        // Language switcher
        document.getElementById('langBtn')?.addEventListener('click', function() {
            document.getElementById('langDropdown')?.classList.toggle('active');
        });

        // User menu
        document.getElementById('userBtn')?.addEventListener('click', function() {
            document.getElementById('userDropdown')?.classList.toggle('active');
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.language-switcher')) {
                document.getElementById('langDropdown')?.classList.remove('active');
            }
            if (!event.target.closest('.user-menu')) {
                document.getElementById('userDropdown')?.classList.remove('active');
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            });
        }, 5000);
    </script>
</body>
</html>
