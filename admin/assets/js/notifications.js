// Notifications & Announcements Modal Handler and Client Validation
document.addEventListener('DOMContentLoaded', function () {
    var bModal = document.getElementById('broadcastModal');
    var openBtn = document.getElementById('openBroadcastModal');
    var closeBtn = document.getElementById('closeBroadcastBtn');
    var cancelBtn = document.getElementById('cancelBroadcastBtn');

    if (openBtn && bModal) {
        openBtn.addEventListener('click', function () { bModal.classList.add('open'); });
    }

    [closeBtn, cancelBtn].forEach(function (el) {
        if (el && bModal) {
            el.addEventListener('click', function () { bModal.classList.remove('open'); });
        }
    });

    if (bModal) {
        bModal.addEventListener('click', function (e) {
            if (e.target === bModal) bModal.classList.remove('open');
        });

        var form = bModal.querySelector('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                var titleEl = form.querySelector('[name="title"]');
                var msgEl = form.querySelector('[name="message"]');

                if (titleEl && !titleEl.value.trim()) {
                    e.preventDefault();
                    alert('Please enter an announcement title.');
                    titleEl.focus();
                    return;
                }

                if (msgEl && msgEl.value.trim().length < 5) {
                    e.preventDefault();
                    alert('Announcement message must be at least 5 characters long.');
                    msgEl.focus();
                    return;
                }
            });
        }
    }
});
