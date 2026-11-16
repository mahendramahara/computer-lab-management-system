var modal = document.getElementById('labModal');
var form  = document.getElementById('labForm');

// Open the popup empty (add) or filled with a lab (edit)
function openModal(lab) {
    form.reset();

    document.getElementById('labModalTitle').textContent = lab ? 'Edit Lab' : 'Add Lab';
    form.elements.task.value = lab ? 'edit' : 'add';
    form.elements.lab_id.value = lab ? lab.id : '';

    if (lab) {
        Object.keys(lab).forEach(function (key) {
            if (form.elements[key] && lab[key] !== null) {
                form.elements[key].value = lab[key];
            }
        });
    }

    modal.classList.add('open');
}

function closeModal() {
    modal.classList.remove('open');
}

document.getElementById('labAdd').addEventListener('click', function () {
    openModal(null);
});

document.querySelectorAll('.labs-edit').forEach(function (button) {
    button.addEventListener('click', function () {
        openModal(JSON.parse(button.dataset.lab));
    });
});

// Close with the X button, outside click or Escape key
document.getElementById('labClose').addEventListener('click', closeModal);

modal.addEventListener('click', function (event) {
    if (event.target === modal) {
        closeModal();
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

// Form validation before submit
if (form) {
    form.addEventListener('submit', function (event) {
        var name = form.elements['lab_name'] ? form.elements['lab_name'].value.trim() : '';
        var code = form.elements['lab_code'] ? form.elements['lab_code'].value.trim() : '';
        var room = form.elements['room_number'] ? form.elements['room_number'].value.trim() : '';

        if (!name) {
            event.preventDefault();
            alert('Please enter a Lab Name.');
            if (form.elements['lab_name']) form.elements['lab_name'].focus();
            return;
        }

        if (!code) {
            event.preventDefault();
            alert('Please enter a Lab Code (e.g. LAB-01).');
            if (form.elements['lab_code']) form.elements['lab_code'].focus();
            return;
        }

        if (!room) {
            event.preventDefault();
            alert('Please enter a Room Number.');
            if (form.elements['room_number']) form.elements['room_number'].focus();
            return;
        }
    });
}

// Ask before deleting
document.querySelectorAll('.labs-delete').forEach(function (deleteForm) {
    deleteForm.addEventListener('submit', function (event) {
        if (!confirm('Delete ' + deleteForm.dataset.name + '?')) {
            event.preventDefault();
        }
    });
});