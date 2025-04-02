</div><!-- End of #content -->
    </div><!-- End of #wrapper -->

    <!-- JavaScript for Mobile Toggle and Sidebar -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile sidebar toggle
        const mobileToggle = document.getElementById('mobile-toggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.overlay');
        const content = document.getElementById('content');
        
        if (mobileToggle) {
            mobileToggle.classList.remove('d-none');
            
            mobileToggle.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-active');
                overlay.classList.toggle('active');
                
                // Toggle content margin for mobile
                if (sidebar.classList.contains('mobile-active')) {
                    content.style.marginLeft = 'var(--sidebar-expanded-width)';
                } else {
                    content.style.marginLeft = '0';
                }
            });
        }
        
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-active');
                overlay.classList.remove('active');
                content.style.marginLeft = '0';
            });
        }

        // Make mobile toggle visible
        mobileToggle.classList.remove('d-none');

        // Logout button functionality with 1-second delay
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Logout',
                    text: 'Are you sure you want to logout?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, logout'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading indicator with 1-second delay
                        Swal.fire({
                            title: 'Logging out...',
                            timer: 1000,
                            timerProgressBar: true,
                            didOpen: () => {
                                Swal.showLoading()
                            },
                            willClose: () => {
                                window.location.href = 'logout.php';
                            }
                        });
                    }
                });
            });
        }
    });
</script>
</body>
</html>