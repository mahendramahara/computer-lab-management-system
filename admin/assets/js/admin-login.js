// Admin Login Interactive Scripts
document.addEventListener('DOMContentLoaded', function () {
    var toggleBtn = document.getElementById('togglePassword');
    var passwordInput = document.getElementById('password');
    var eyeIcon = document.getElementById('eyeIcon');
    var form = document.querySelector('form');
    var usernameInput = document.getElementById('username');

    if (toggleBtn && passwordInput && eyeIcon) {
        toggleBtn.addEventListener('click', function () {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            var user = usernameInput ? usernameInput.value.trim() : '';
            var pass = passwordInput ? passwordInput.value : '';

            if (!user) {
                e.preventDefault();
                alert('Please enter your administrator username or email.');
                if (usernameInput) usernameInput.focus();
                return;
            }

            if (!pass) {
                e.preventDefault();
                alert('Please enter your administrator password.');
                if (passwordInput) passwordInput.focus();
                return;
            }
        });
    }
});
