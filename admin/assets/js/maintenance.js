// Maintenance Records Management Script & Client Validation
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('maintModal');
    var form  = document.getElementById('maintForm');
    var addBtn = document.getElementById('addMaintBtn');
    var closeBtn = document.getElementById('maintClose');
    var cancelBtn = document.getElementById('maintCancel');

    if (addBtn && modal && form) {
        addBtn.addEventListener('click', function () {
            form.reset();
            var taskEl = document.getElementById('maintTask');
            var idEl = document.getElementById('maintId');
            var titleEl = document.getElementById('maintModalTitle');
            var dateEl = document.getElementById('form_date');

            if (taskEl) taskEl.value = 'add';
            if (idEl) idEl.value = '';
            if (titleEl) titleEl.textContent = 'New Maintenance Record';
            if (dateEl) dateEl.value = new Date().toISOString().split('T')[0];

            modal.classList.add('open');
        });
    }

    document.querySelectorAll('.edit-maint-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!form || !modal) return;
            try {
                var m = JSON.parse(btn.dataset.maint);
                form.reset();
                document.getElementById('maintTask').value = 'edit';
                document.getElementById('maintId').value = m.id;
                document.getElementById('maintModalTitle').textContent = 'Edit Record #MN-' + String(m.id).padStart(3, '0');
                document.getElementById('form_computer_id').value = m.computer_id;
                document.getElementById('form_technician_id').value = m.technician_id || '';
                document.getElementById('form_component').value = m.component;
                document.getElementById('form_type').value = m.maintenance_type;
                document.getElementById('form_date').value = m.maintenance_date;
                document.getElementById('form_status').value = m.status;
                document.getElementById('form_problem').value = m.problem_description;
                document.getElementById('form_repair').value = m.repair_description || '';
                document.getElementById('form_cost').value = m.repair_cost;
                modal.classList.add('open');
            } catch (err) {
                console.error('Failed to parse maintenance data', err);
            }
        });
    });

    [closeBtn, cancelBtn].forEach(function (el) {
        if (el && modal) {
            el.addEventListener('click', function () { modal.classList.remove('open'); });
        }
    });

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) modal.classList.remove('open');
        });
    }

    // Form validation
    if (form) {
        form.addEventListener('submit', function (e) {
            var compEl = document.getElementById('form_computer_id');
            var probEl = document.getElementById('form_problem');
            var costEl = document.getElementById('form_cost');

            if (compEl && !compEl.value) {
                e.preventDefault();
                alert('Please select a computer.');
                compEl.focus();
                return;
            }

            if (probEl && probEl.value.trim().length < 5) {
                e.preventDefault();
                alert('Please provide a descriptive problem explanation (at least 5 characters).');
                probEl.focus();
                return;
            }

            if (costEl && costEl.value !== '') {
                var cost = parseFloat(costEl.value);
                if (isNaN(cost) || cost < 0) {
                    e.preventDefault();
                    alert('Cost cannot be negative.');
                    costEl.focus();
                    return;
                }
            }
        });
    }
});
