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
        
        <?php if(isset($_SESSION['user_level']) && $_SESSION['user_level'] !== 'inspector'): ?>
        <li>
            <a href="user.php" class="<?php echo ($current_page == 'user.php') ? 'active' : ''; ?>">
                <div class="icon-container">
                    <i class="fas fa-users"></i>
                </div>
                <span class="nav-text">User Management</span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Modified Utilities dropdown menu with admin-only access to specific pages -->
        <li>
            <a href="#utilitiesSubmenu" data-toggle="collapse" aria-expanded="<?php echo (in_array($current_page, ['audit_logs.php', 'backup_restore.php'])) ? 'true' : 'false'; ?>" class="dropdown-toggle <?php echo (in_array($current_page, ['audit_logs.php', 'backup_restore.php'])) ? 'active' : ''; ?>">
                <div class="icon-container">
                    <i class="fas fa-tools"></i>
                </div>
                <span class="nav-text">Utilities</span>
            </a>
            <ul class="collapse list-unstyled <?php echo (in_array($current_page, ['audit_logs.php', 'backup_restore.php'])) ? 'show' : ''; ?>" id="utilitiesSubmenu">
    <?php if(isset($_SESSION['user_level']) && $_SESSION['user_level'] === 'admin'): ?>
    <li>
        <a href="audit_logs.php" class="<?php echo ($current_page == 'audit_logs.php') ? 'active' : ''; ?>">
            <div class="icon-container">
                <i class="fas fa-history"></i>
            </div>
            <span class="nav-text">Audit Logs</span>
        </a>
    </li>
    <li>
        <!-- CHANGE THIS LINE - Update the path to backup_restore.php -->
        <a href="backup_restore.php" class="<?php echo ($current_page == 'backup_restore.php') ? 'active' : ''; ?>">
            <div class="icon-container">
                <i class="fas fa-database"></i>
            </div>
            <span class="nav-text">Backup & Restore</span>
        </a>
    </li>
    <?php else: ?>
    <!-- ... rest of the code ... -->
    <?php endif; ?>
</ul>
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

<!-- JavaScript to handle dropdown menu and navigation -->
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
    
    // Toggle submenu on click
    document.querySelectorAll('.dropdown-toggle').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            const submenu = document.querySelector(this.getAttribute('href'));
            if (submenu) {
                submenu.classList.toggle('show');
                this.setAttribute('aria-expanded', submenu.classList.contains('show'));
            }
        });
    });
    
    // Make submenu items clickable without toggling parent
    document.querySelectorAll('#utilitiesSubmenu a').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent event bubbling to parent dropdown toggle
            // Normal navigation allowed to proceed
        });
    });
    
    // Keep submenu open for active pages
    document.querySelectorAll('.dropdown-toggle.active').forEach(function(element) {
        const submenuId = element.getAttribute('href');
        const submenu = document.querySelector(submenuId);
        if (submenu && !submenu.classList.contains('show')) {
            submenu.classList.add('show');
            element.setAttribute('aria-expanded', 'true');
        }
    });
    
    // Logout button functionality with confirmation
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
                    
                    // Add delay before redirecting
                    setTimeout(() => {
                        window.location.href = 'logout.php';
                    }, 1000);
                }
            });
        });
    }
});
</script>

<style>
/* Add this to your CSS for disabled menu items */
.disabled-menu-item {
    cursor: not-allowed;
    opacity: 0.6;
    border-left: 3px solid #ccc;
}
</style>