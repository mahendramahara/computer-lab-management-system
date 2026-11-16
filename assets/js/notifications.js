document.addEventListener('DOMContentLoaded', function () {
    var tabs = document.querySelectorAll('.notifications-tab');
    var items = document.querySelectorAll('.notifications-item');

    // ── Filter Tabs ───────────────────────────────────────────
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var filter = this.dataset.filter;

            tabs.forEach(function (t) { t.classList.remove('active'); });
            this.classList.add('active');

            items.forEach(function (item) {
                var isUnread = item.dataset.unread === '1';
                var group    = item.dataset.group;

                if (filter === 'all') {
                    item.style.display = 'flex';
                } else if (filter === 'unread') {
                    item.style.display = isUnread ? 'flex' : 'none';
                } else if (filter === 'lab') {
                    item.style.display = (group === 'lab') ? 'flex' : 'none';
                } else if (filter === 'general') {
                    item.style.display = (group === 'general') ? 'flex' : 'none';
                }
            });
        });
    });

    // ── Single Mark as Read (AJAX) ────────────────────────────
    document.querySelectorAll('.btn-mark-read').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var notifId = this.dataset.id;
            var item = this.closest('.notifications-item');
            var self = this;

            var formData = new FormData();
            formData.append('action', 'mark_read');
            formData.append('notification_id', notifId);

            fetch('notifications.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    item.classList.remove('unread');
                    item.dataset.unread = '0';

                    // Update UI actions
                    var actionsDiv = item.querySelector('.notifications-actions');
                    if (actionsDiv) {
                        actionsDiv.innerHTML = '<span class="notifications-read-check" title="Read"><i class="fa-solid fa-check"></i></span>';
                    }

                    // Update unread count badge in tab
                    var unreadTab = document.querySelector('.notifications-tab[data-filter="unread"] .notifications-pill');
                    if (unreadTab) {
                        var curCount = parseInt(unreadTab.textContent, 10);
                        if (!isNaN(curCount) && curCount > 0) {
                            unreadTab.textContent = curCount - 1;
                        }
                    }

                    // Update topbar bell indicator if count reaches 0
                    var topbarBell = document.querySelector('.topbar-bell');
                    var remainingUnread = document.querySelectorAll('.notifications-item.unread').length;
                    if (topbarBell && remainingUnread === 0) {
                        topbarBell.classList.remove('has-unread');
                    }
                }
            })
            .catch(function () {
                // Fallback standard submit if fetch fails
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'notifications.php';
                var aInput = document.createElement('input');
                aInput.type = 'hidden';
                aInput.name = 'action';
                aInput.value = 'mark_read';
                var idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'notification_id';
                idInput.value = notifId;
                form.appendChild(aInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            });
        });
    });
});
