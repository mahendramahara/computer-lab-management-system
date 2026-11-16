// Profile page interactive scripts
document.addEventListener('DOMContentLoaded', function () {
    var image    = document.getElementById('image');
    var preview  = document.getElementById('profilePreview');
    var letter   = document.getElementById('profileLetter');
    var fileName = document.getElementById('profileFileName');
    var pwForm   = document.getElementById('passwordForm');

    // Show the selected photo before saving and check its size (max 2MB)
    if (image) {
        image.addEventListener('change', function () {
            var file = image.files[0];

            if (file && file.size > 2 * 1024 * 1024) {
                alert('Photo size must be less than 2MB.');
                image.value = '';
                file = null;
            }

            if (fileName) {
                fileName.textContent = file ? file.name : 'Choose new photo';
            }

            if (file && preview) {
                preview.src = URL.createObjectURL(file);
                preview.hidden = false;
                if (letter) {
                    letter.hidden = true;
                }
            }
        });
    }

    // New password and confirm password validation
    if (pwForm) {
        pwForm.addEventListener('submit', function (event) {
            var newPassword = document.getElementById('new_password').value;
            var confirm     = document.getElementById('confirm_password').value;

            if (newPassword !== confirm) {
                event.preventDefault();
                alert('New password and confirm password do not match.');
            }
        });
    }
});
