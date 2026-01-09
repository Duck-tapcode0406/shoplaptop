    </main>
    
    <script>
        // Mobile sidebar toggle
        document.querySelector('.mobile-menu-btn')?.addEventListener('click', function() {
            document.querySelector('.admin-sidebar').classList.toggle('active');
        });

        // Search functionality
        document.querySelector('.header-search input')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    window.location.href = 'search.php?q=' + encodeURIComponent(query);
                }
            }
        });

        // Confirm delete
        function confirmDelete(message) {
            return confirm(message || 'Bạn có chắc chắn muốn xóa?');
        }

        // Format currency
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        }

        // Auto-hide alerts
        document.querySelectorAll('.alert').forEach(function(alert) {
            setTimeout(function() {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }, 5000);
        });
    </script>
</body>
</html>



