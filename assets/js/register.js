// Register Page Interactive Scripts & Client-Side Validation
function toggleRegisterPassword(inputId, iconId) {
    var inputField = document.getElementById(inputId);
    var eyeIcon = document.getElementById(iconId);

    if (!inputField || !eyeIcon) return;

    if (inputField.type === "password") {
        inputField.type = "text";
        eyeIcon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        inputField.type = "password";
        eyeIcon.classList.replace("fa-eye-slash", "fa-eye");
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('register-form');
    var nameInput = document.getElementById('register-name');
    var rollInput = document.getElementById('register-roll');
    var emailInput = document.getElementById('register-email');
    var passInput = document.getElementById('register-password');
    var confInput = document.getElementById('register-confirm-password');
    var termsInput = document.getElementById('register-terms');

    function setFieldStatus(inputEl, boxId, errId, isValid, errMsg) {
        var box = document.getElementById(boxId);
        var err = document.getElementById(errId);
        if (err && errMsg) err.textContent = errMsg;

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

    function validateName() {
        if (!nameInput) return true;
        var v = nameInput.value.trim();
        return setFieldStatus(nameInput, 'box-register-name', 'err-register-name', v.length >= 3, 'Full name must be at least 3 characters.');
    }

    function validateRoll() {
        if (!rollInput) return true;
        var v = rollInput.value.trim();
        return setFieldStatus(rollInput, 'box-register-roll', 'err-register-roll', v.length >= 3, 'Roll number must be at least 3 characters.');
    }

    function validateEmail() {
        if (!emailInput) return true;
        var v = emailInput.value.trim();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return setFieldStatus(emailInput, 'box-register-email', 'err-register-email', emailRegex.test(v), 'Please enter a valid email address.');
    }

    function validatePassword() {
        if (!passInput) return true;
        var v = passInput.value;
        var ok = v.length >= 6;
        setFieldStatus(passInput, 'box-register-password', 'err-register-password', ok, 'Password must be at least 6 characters.');
        if (confInput && confInput.value.length > 0) validateConfirm();
        return ok;
    }

    function validateConfirm() {
        if (!confInput || !passInput) return true;
        var p = passInput.value;
        var c = confInput.value;
        var match = c.length >= 6 && p === c;
        var okMsg = document.getElementById('ok-register-confirm');

        setFieldStatus(confInput, 'box-register-confirm', 'err-register-confirm', match, p !== c ? 'Passwords do not match.' : 'Confirm password must be at least 6 characters.');
        if (okMsg) {
            if (match) okMsg.classList.add('visible');
            else okMsg.classList.remove('visible');
        }
        return match;
    }

    function validateTerms() {
        if (!termsInput) return true;
        var err = document.getElementById('err-register-terms');
        if (!termsInput.checked) {
            if (err) err.classList.add('visible');
            return false;
        } else {
            if (err) err.classList.remove('visible');
            return true;
        }
    }

    // Real-time user input & blur event listeners
    if (nameInput) {
        nameInput.addEventListener('input', validateName);
        nameInput.addEventListener('blur', validateName);
    }

    if (rollInput) {
        rollInput.addEventListener('input', validateRoll);
        rollInput.addEventListener('blur', validateRoll);
    }

    if (emailInput) {
        emailInput.addEventListener('input', validateEmail);
        emailInput.addEventListener('blur', validateEmail);
    }

    if (passInput) {
        passInput.addEventListener('input', validatePassword);
        passInput.addEventListener('blur', validatePassword);
    }

    if (confInput) {
        confInput.addEventListener('input', validateConfirm);
        confInput.addEventListener('blur', validateConfirm);
    }

    if (termsInput) {
        termsInput.addEventListener('change', validateTerms);
    }

    // Form Submit Interceptor
    if (form) {
        form.addEventListener('submit', function (e) {
            var vName  = validateName();
            var vRoll  = validateRoll();
            var vEmail = validateEmail();
            var vPass  = validatePassword();
            var vConf  = validateConfirm();
            var vTerms = validateTerms();

            if (!vName || !vRoll || !vEmail || !vPass || !vConf || !vTerms) {
                e.preventDefault();
                // Smoothly focus first invalid field
                if (!vName && nameInput) nameInput.focus();
                else if (!vRoll && rollInput) rollInput.focus();
                else if (!vEmail && emailInput) emailInput.focus();
                else if (!vPass && passInput) passInput.focus();
                else if (!vConf && confInput) confInput.focus();
                else if (!vTerms && termsInput) termsInput.focus();
            } else {
                var btn = document.getElementById('register-submit-btn');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating Account...';
                }
            }
        });
    }
});
