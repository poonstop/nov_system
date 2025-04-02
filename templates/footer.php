<footer class="bg-light text-center text-lg-start mt-5">
        <div class="text-center p-3">
            © 2025 NOV System: All rights reserved.
        </div>
    </footer>

    <!-- Add SweetAlert JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');
        const mobileToggle = document.getElementById('mobile-toggle');
        const overlay = document.querySelector('.overlay');
        
        // Handle mobile toggle button
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-active');
            overlay.classList.toggle('active');
        });
        
        // Close sidebar when clicking overlay (mobile only)
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('mobile-active');
            overlay.classList.remove('active');
        });
        
        // Logout confirmation
        document.getElementById('logoutBtn').addEventListener('click', function() {
            Swal.fire({
                title: 'Confirm Logout',
                text: 'Are you sure you want to log out of the NOVM System?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, logout!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show logout progress with countdown
                    let timerInterval;
                    Swal.fire({
                        title: 'Logging out...',
                        html: 'You will be redirected in <b></b> seconds.',
                        timer: 2000, // 2 seconds delay
                        timerProgressBar: true,
                        allowOutsideClick: false,
                        didOpen: () => {
                            const b = Swal.getHtmlContainer().querySelector('b');
                            timerInterval = setInterval(() => {
                                b.textContent = (Swal.getTimerLeft() / 1000).toFixed(1);
                            }, 100);
                        },
                        willClose: () => {
                            clearInterval(timerInterval);
                        }
                    }).then((result) => {
                        // Perform the actual logout when timer completes
                        if (result.dismiss === Swal.DismissReason.timer) {
                            window.location.href = 'logout.php';
                        }
                    });
                }
            });
        });
    });
    </script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/scripts.js"></script>
</body>
</html>