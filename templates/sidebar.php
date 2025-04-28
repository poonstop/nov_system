<?php
// Get current page name for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<nav id="sidebar">
    <div class="sidebar-header">
        <img src="../images/dti-logo1.png" alt="DTI Logo" class="sidebar-logo">
        <div class="title-container">
            <h5 class="sidebar-title mb-0">Monitoring and Enforcement Tracking System Non - Compliance</h5>
        </div>
    </div>

    <div class="user-profile">
        <div class="d-flex align-items-center">
            <div class="icon-container">
                <i class="fas fa-user-circle fa-lg"></i>
            </div>
            <div class="user-info">
                <strong>Welcome,</strong>
                <?php echo htmlspecialchars(ucfirst($_SESSION['username'] ?? 'User')); ?>
            </div>
        </div>
    </div>

    <ul class="list-unstyled components">
        <li>
            <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <div class="icon-container">
                    <i class="fas fa-home"></i>
                </div>
                <span class="nav-text">Home</span>
            </a>
        </li>
        <li>
        <a href="establishments.php" class="<?php echo ($current_page == 'establishments.php') ? 'active' : ''; ?>">
                <div class="icon-container">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <span class="nav-text">Notice Management</span>
            </a>
        </li>
        <li>
            <a href="nov_form.php" class="<?php echo ($current_page == 'nov_form.php') ? 'active' : ''; ?>">
                <div class="icon-container">
                    <i class="fas fa-building"></i>
                </div>
                <span class="nav-text">Establishments Management</span>
            </a>
        </li>
        <li>
            <a href="user.php" class="<?php echo ($current_page == 'user.php') ? 'active' : ''; ?>">
                <div class="icon-container">
                    <i class="fas fa-users"></i>
                </div>
                <span class="nav-text">User Management</span>
            </a>
        </li>
    </ul>
    
    <div class="logout-section">
        <button id="logoutBtn" class="btn btn-danger w-100 d-flex align-items-center">
            <div class="icon-container">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <span class="btn-text">Logout</span>
        </button>
    </div>
</nav>

<!-- JavaScript for Mobile Toggle -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile sidebar toggle
    const mobileToggle = document.getElementById('mobile-toggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.overlay');
    
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-active');
            overlay.classList.toggle('active');
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('mobile-active');
            overlay.classList.remove('active');
        });
    }
    
    // Make mobile toggle visible
    if (mobileToggle) mobileToggle.classList.remove('d-none');
    
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
                    // Show loading state
                    logoutBtn.disabled = true;
                    logoutBtn.innerHTML = `
                        <div class="icon-container">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <span class="btn-text">Logging out...</span>
                    `;
                    
                    // Add 1-second delay before redirecting
                    setTimeout(() => {
                        window.location.href = 'logout.php';
                    }, 1000);
                }
            });
        });
    }
});
</script>