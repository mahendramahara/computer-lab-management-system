// Login Page Interactive Scripts & Client-Side Validation
function toggleLoginPassword() {
    var passwordInput = document.getElementById("login-password");
    var eyeIcon = document.getElementById("login-eye-icon");

    if (!passwordInput || !eyeIcon) return;

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeIcon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        passwordInput.type = "password";
        eyeIcon.classList.replace("fa-eye-slash", "fa-eye");
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('login-form');
    var usernameInput = document.getElementById('login-username');
    var passwordInput = document.getElementById('login-password');
    var submitBtn = document.getElementById('login-submit-btn');

    function setFieldStatus(inputEl, boxId, errId, isValid) {
        var box = document.getElementById(boxId);
        var err = document.getElementById(errId);

        if (!box) return isValid;

        if (isValid) {
            box.classList.remove('is-invalid');
            box.classList.add('is-valid');
            if (err) err.classList.remove('visible');
        } else {
            box.classList.remove('is-valid');
            box.classList.add('is-invalid');
            if (err) err.classList.add('visible');
        }
        return isValid;
    }

    function validateUsername() {
        if (!usernameInput) return true;
        var v = usernameInput.value.trim();
        return setFieldStatus(usernameInput, 'box-login-username', 'err-login-username', v.length > 0);
    }

    function validatePassword() {
        if (!passwordInput) return true;
        var v = passwordInput.value;
        return setFieldStatus(passwordInput, 'box-login-password', 'err-login-password', v.length > 0);
    }

    // Real-time input & blur event listeners
    if (usernameInput) {
        usernameInput.addEventListener('input', validateUsername);
        usernameInput.addEventListener('blur', validateUsername);
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', validatePassword);
        passwordInput.addEventListener('blur', validatePassword);
    }

    // Form Submit Interceptor
    if (form) {
        form.addEventListener('submit', function (e) {
            var vUser = validateUsername();
            var vPass = validatePassword();

            if (!vUser || !vPass) {
                e.preventDefault();
                if (!vUser && usernameInput) usernameInput.focus();
                else if (!vPass && passwordInput) passwordInput.focus();
            } else {
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Logging in...';
                }
            }
        });
    }
});
