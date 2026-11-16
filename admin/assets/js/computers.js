// Computer Management Interactive Script & Validation
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('computerModal');
    var form  = document.getElementById('computerForm');
    var addBtn = document.getElementById('computerAdd');
    var closeBtn = document.getElementById('computerClose');

    // Open popup empty (add) or populated (edit)
    function openModal(computer) {
        if (!form || !modal) return;
        form.reset();

        var titleEl = document.getElementById('computerModalTitle');
        if (titleEl) {
            titleEl.textContent = computer ? 'Edit Computer: ' + (computer.computer_code || '') : 'Add Computer';
        }

        if (form.elements['task']) form.elements['task'].value = computer ? 'edit' : 'add';
        if (form.elements['computer_id']) form.elements['computer_id'].value = computer ? (computer.id || '') : '';

        if (computer) {
            Object.keys(computer).forEach(function (key) {
                if (form.elements[key] && computer[key] !== null && computer[key] !== undefined) {
                    form.elements[key].value = computer[key];
                }
            });
        }

        modal.classList.add('open');
    }

    function closeModal() {
        if (modal) modal.classList.remove('open');
    }

    // Add computer button
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            openModal(null);
        });
    }

    // Edit computer buttons (support both direct click and event delegation)
    document.querySelectorAll('.computers-edit-corner, .computers-edit').forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            try {
                var compData = JSON.parse(button.dataset.computer);
                openModal(compData);
            } catch (err) {
                console.error('Failed to parse computer dataset', err);
            }
        });
    });

    // Delegation fallback for edit button clicks anywhere in grid
    document.addEventListener('click', function (e) {
        var editBtn = e.target.closest('.computers-edit-corner, .computers-edit');
        if (editBtn && editBtn.dataset.computer) {
            e.preventDefault();
            try {
                var compData = JSON.parse(editBtn.dataset.computer);
                openModal(compData);
            } catch (err) {
                console.error('Failed to parse computer dataset', err);
            }
        }
    });

    // Close with X button, outside backdrop click, or Escape key
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    // Pre-submission client-side validation
    if (form) {
        form.addEventListener('submit', function (event) {
            var code = form.elements['computer_code'] ? form.elements['computer_code'].value.trim() : '';
            var lab  = form.elements['lab_id'] ? form.elements['lab_id'].value : '';
            var status = form.elements['status'] ? form.elements['status'].value : '';

            if (!code) {
                event.preventDefault();
                alert('Please enter a Computer Code (e.g. PC-001).');
                if (form.elements['computer_code']) form.elements['computer_code'].focus();
                return;
            }

            if (!lab) {
                event.preventDefault();
                alert('Please select a Computer Lab.');
                if (form.elements['lab_id']) form.elements['lab_id'].focus();
                return;
            }

            if (!status) {
                event.preventDefault();
                alert('Please select a Computer Status.');
                if (form.elements['status']) form.elements['status'].focus();
                return;
            }
        });
    }

    // Delete confirmation (delegation and direct)
    document.addEventListener('submit', function (event) {
        var deleteForm = event.target.closest('.computers-delete-corner, .computers-delete');
        if (deleteForm) {
            var pcName = deleteForm.dataset.name || 'this computer';
            if (!confirm('Are you sure you want to delete ' + pcName + '?')) {
                event.preventDefault();
            }
        }
    });

    // Auto-open modal if URL contains add parameter
    if (location.search.indexOf('add=1') !== -1 || location.search.indexOf('action=add') !== -1) {
        openModal(null);
    }
});
