// User Management Modal & Client-Side Validation
document.addEventListener('DOMContentLoaded', function () {
    var modal     = document.getElementById('userModal');
    var form      = document.getElementById('userForm');
    var addBtn    = document.getElementById('addUserBtn');
    var closeBtn  = document.getElementById('userClose');
    var cancelBtn = document.getElementById('userCancel');
    var passField = document.getElementById('form_password');
    var passLabel = document.getElementById('passLabel');
    var passHint  = document.getElementById('passHint');

    if (!modal || !form) return;

    if (addBtn) {
        addBtn.addEventListener('click', function () {
            form.reset();
            document.getElementById('userTask').value = 'add';
            document.getElementById('userId').value = '';
            document.getElementById('userModalTitle').textContent = 'Add New User';
            if (passField) passField.required = true;
            if (passLabel) passLabel.textContent = 'Password *';
            if (passHint) passHint.style.display = 'none';
            modal.classList.add('open');
        });
    }

    document.querySelectorAll('.edit-user-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var u = JSON.parse(btn.dataset.user);
            form.reset();
            document.getElementById('userTask').value = 'edit';
            document.getElementById('userId').value = u.id;
            document.getElementById('userModalTitle').textContent = 'Edit User: ' + u.name;
            document.getElementById('form_name').value = u.name;
            document.getElementById('form_email').value = u.email;
            document.getElementById('form_roll').value = u.roll_number || '';
            document.getElementById('form_role').value = u.role;
            document.getElementById('form_program').value = u.program || '';
            document.getElementById('form_semester').value = u.semester || '';
            document.getElementById('form_status').value = u.status;
            if (passField) passField.required = false;
            if (passLabel) passLabel.textContent = 'Change Password';
            if (passHint) passHint.style.display = 'block';
            modal.classList.add('open');
        });
    });

    [closeBtn, cancelBtn].forEach(function (el) {
        if (el) el.addEventListener('click', function () { modal.classList.remove('open'); });
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.classList.remove('open');
    });

    // User form live client validation
    form.addEventListener('submit', function (e) {
        var nameEl  = document.getElementById('form_name');
        var emailEl = document.getElementById('form_email');
        var roleEl  = document.getElementById('form_role');
        var rollEl  = document.getElementById('form_roll');
        var task    = document.getElementById('userTask').value;
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!nameEl.value.trim() || nameEl.value.trim().length < 3) {
            e.preventDefault();
            alert('Please enter a valid full name (at least 3 characters).');
            nameEl.focus();
            return;
        }

        if (!emailRegex.test(emailEl.value.trim())) {
            e.preventDefault();
            alert('Please enter a valid email address.');
            emailEl.focus();
            return;
        }

        if (roleEl.value === 'student' && !rollEl.value.trim()) {
            e.preventDefault();
            alert('Please enter a roll number for student accounts.');
            rollEl.focus();
            return;
        }

        if (task === 'add' && (!passField.value || passField.value.length < 6)) {
            e.preventDefault();
            alert('Password is required and must be at least 6 characters.');
            passField.focus();
            return;
        }

        if (task === 'edit' && passField.value && passField.value.length < 6) {
            e.preventDefault();
            alert('New password must be at least 6 characters if you wish to change it.');
            passField.focus();
            return;
        }
    });
});
