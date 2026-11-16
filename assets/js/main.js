// ============== Sidebar (mobile) ==============
var sidebar = document.getElementById('sidebar');
var toggleButton = document.getElementById('sidebarToggle');

if (toggleButton) {
    toggleButton.addEventListener('click', function () {
        sidebar.classList.toggle('open');
    });
}

// ============== User dropdown in header ==============
var userMenu = document.getElementById('userMenu');

if (userMenu) {
    userMenu.addEventListener('click', function () {
        userMenu.classList.toggle('open');
    });

    // Close when clicking anywhere else
    document.addEventListener('click', function (event) {
        if (!userMenu.contains(event.target)) {
            userMenu.classList.remove('open');
        }
    });
}

// ============== Ask before deleting: <form data-confirm="Are you sure?"> ==============
var confirmForms = document.querySelectorAll('form[data-confirm]');
confirmForms.forEach(function (form) {
    form.addEventListener('submit', function (event) {
        if (!confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

// ============== Hide alert messages after 5 seconds ==============
setTimeout(function () {
    document.querySelectorAll('.alert').forEach(function (alertBox) {
        alertBox.remove();
    });
}, 5000);