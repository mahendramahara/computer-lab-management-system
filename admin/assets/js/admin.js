// Admin panel general interaction scripts
document.addEventListener('DOMContentLoaded', function () {
    // 1. Sidebar toggle
    var toggleBtn = document.getElementById('adminToggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            document.body.classList.toggle('admin-toggled');
        });
    }

    // 2. Admin user dropdown in topbar
    var adminUserMenu = document.getElementById('adminUserMenu');
    if (adminUserMenu) {
        adminUserMenu.addEventListener('click', function (e) {
            if (!e.target.closest('a')) {
                adminUserMenu.classList.toggle('open');
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!adminUserMenu.contains(e.target)) {
                adminUserMenu.classList.remove('open');
            }
        });

        // Close dropdown on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                adminUserMenu.classList.remove('open');
            }
        });
    }
});
