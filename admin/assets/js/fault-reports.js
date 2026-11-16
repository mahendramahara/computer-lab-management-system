// Fault Reports Management Interactive Scripts & Client Validation
var photoModal = document.getElementById('photoModal');
var photoModalImg = document.getElementById('photoModalImg');
var photoModalTitle = document.getElementById('photoModalTitle');
var closePhotoModal = document.getElementById('closePhotoModal');
var currentEvidenceUrl = '';

function viewFaultPhoto(url, title) {
    if (!photoModal || !photoModalImg) return;
    photoModalImg.src = url;
    if (photoModalTitle && title) {
        photoModalTitle.innerHTML = '<i class="fa-solid fa-camera" style="color: #1d63e8; margin-right: 6px;"></i> ' + title;
    }
    photoModal.classList.add('open');
}

function openEvidenceFull() {
    if (currentEvidenceUrl) {
        var pc = document.getElementById('modal_pc_code') ? document.getElementById('modal_pc_code').textContent : '';
        var title = document.getElementById('modal_issue_title') ? document.getElementById('modal_issue_title').textContent : '';
        viewFaultPhoto(currentEvidenceUrl, pc + ' - ' + title);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    if (closePhotoModal && photoModal) {
        closePhotoModal.addEventListener('click', function () { photoModal.classList.remove('open'); });
    }
    if (photoModal) {
        photoModal.addEventListener('click', function (e) {
            if (e.target === photoModal) photoModal.classList.remove('open');
        });
    }

    // Review Modal
    var reviewModal = document.getElementById('reviewModal');
    var reviewClose = document.getElementById('reviewClose');
    var modalCancel = document.getElementById('modalCancelBtn');

    document.querySelectorAll('.review-report-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var r = JSON.parse(btn.dataset.report);
            document.getElementById('modal_report_id').value = r.id;
            document.getElementById('reviewModalTitle').textContent = 'Review Fault Report #FR-' + String(r.id).padStart(4, '0');
            
            // Status & Priority Badges
            var sBadge = document.getElementById('modal_status_badge');
            var pBadge = document.getElementById('modal_priority_badge');
            
            var statusClassMap = {
                'pending': 'warning',
                'under_review': 'purple',
                'in_progress': 'primary',
                'resolved': 'success',
                'rejected': 'danger'
            };
            if (sBadge) {
                sBadge.className = 'badge badge-' + (statusClassMap[r.status] || 'primary');
                sBadge.textContent = r.status.replace('_', ' ').toUpperCase();
            }

            var priorityClassMap = {
                'high': 'badge-danger',
                'medium': 'badge-warning',
                'low': 'badge-light'
            };
            if (pBadge) {
                pBadge.className = 'badge ' + (priorityClassMap[r.priority] || 'badge-warning');
                pBadge.textContent = (r.priority ? r.priority.toUpperCase() : 'MEDIUM') + ' PRIORITY';
            }

            // Hardware details
            if (document.getElementById('modal_pc_code')) document.getElementById('modal_pc_code').textContent = r.computer_code;
            if (document.getElementById('modal_lab_name')) document.getElementById('modal_lab_name').textContent = r.lab_name + (r.room_number ? ' (' + r.room_number + ')' : '');
            if (document.getElementById('modal_component_name')) document.getElementById('modal_component_name').textContent = r.component || 'General';

            // Student Reporter details
            if (document.getElementById('modal_student_name')) document.getElementById('modal_student_name').textContent = r.student_name || 'Anonymous Student';
            if (document.getElementById('modal_student_roll')) document.getElementById('modal_student_roll').textContent = r.roll_number || 'No Roll Number';
            if (document.getElementById('modal_student_email')) document.getElementById('modal_student_email').textContent = r.student_email || '—';
            if (document.getElementById('modal_report_date')) {
                document.getElementById('modal_report_date').textContent = new Date(r.reported_at).toLocaleDateString('en-US', {
                    month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
                });
            }

            // Problem & Description
            if (document.getElementById('modal_issue_title')) document.getElementById('modal_issue_title').textContent = r.issue_title;
            if (document.getElementById('modal_issue_desc')) document.getElementById('modal_issue_desc').textContent = r.description;

            // Form Fields
            if (document.getElementById('modal_status')) document.getElementById('modal_status').value = r.status;
            if (document.getElementById('modal_assigned_to')) document.getElementById('modal_assigned_to').value = r.assigned_to || '';
            if (document.getElementById('modal_admin_remarks')) document.getElementById('modal_admin_remarks').value = r.admin_remarks || '';

            // Image Handling
            var imgWrap = document.getElementById('modal_image_wrap');
            var noImg   = document.getElementById('modal_no_image');
            var imgSrc  = document.getElementById('modal_image_src');

            if (r.report_image) {
                var imgPath = (r.report_image.startsWith('http://') || r.report_image.startsWith('https://'))
                    ? r.report_image
                    : (r.report_image.startsWith('uploads/') ? '../' + r.report_image : '../uploads/reports/' + r.report_image);
                currentEvidenceUrl = imgPath;
                if (imgSrc) imgSrc.src = imgPath;
                if (imgWrap) imgWrap.style.display = 'flex';
                if (noImg) noImg.style.display   = 'none';
            } else {
                currentEvidenceUrl = '';
                if (imgWrap) imgWrap.style.display = 'none';
                if (noImg) noImg.style.display   = 'flex';
            }

            if (reviewModal) reviewModal.classList.add('open');
        });
    });

    [reviewClose, modalCancel].forEach(function (el) {
        if (el && reviewModal) el.addEventListener('click', function () { reviewModal.classList.remove('open'); });
    });
    if (reviewModal) {
        reviewModal.addEventListener('click', function (e) {
            if (e.target === reviewModal) reviewModal.classList.remove('open');
        });
    }

    // Add Report Modal
    var addModal = document.getElementById('addReportModal');
    var openAddBtn = document.getElementById('openAddReportBtn');
    var closeAddBtn = document.getElementById('closeAddReportBtn');
    var cancelAddBtn = document.getElementById('cancelAddReportBtn');

    if (openAddBtn && addModal) {
        openAddBtn.addEventListener('click', function () { addModal.classList.add('open'); });
    }
    [closeAddBtn, cancelAddBtn].forEach(function (el) {
        if (el && addModal) el.addEventListener('click', function () { addModal.classList.remove('open'); });
    });
    if (addModal) {
        addModal.addEventListener('click', function (e) {
            if (e.target === addModal) addModal.classList.remove('open');
        });
    }

    // Add report form live validation
    var addReportForm = addModal ? addModal.querySelector('form') : null;
    if (addReportForm) {
        addReportForm.addEventListener('submit', function (e) {
            var pcEl   = document.getElementById('add_pc');
            var compEl = document.getElementById('add_comp');
            var descEl = document.getElementById('add_desc');
            var imgEl  = document.getElementById('add_img');

            if (!pcEl || !pcEl.value) {
                e.preventDefault();
                alert('Please select a computer.');
                if (pcEl) pcEl.focus();
                return;
            }

            if (!compEl || !compEl.value) {
                e.preventDefault();
                alert('Please select the faulty component.');
                if (compEl) compEl.focus();
                return;
            }

            if (!descEl || !descEl.value.trim() || descEl.value.trim().length < 10) {
                e.preventDefault();
                alert('Please enter a problem description (at least 10 characters).');
                if (descEl) descEl.focus();
                return;
            }

            if (imgEl && imgEl.files && imgEl.files[0]) {
                var f = imgEl.files[0];
                var allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
                if (allowed.indexOf(f.type) === -1) {
                    e.preventDefault();
                    alert('Invalid image format. Please select a JPG, PNG, or WebP photo.');
                    imgEl.value = '';
                    return;
                }
                if (f.size > 5 * 1024 * 1024) {
                    e.preventDefault();
                    alert('Image file size exceeds the 5MB limit.');
                    imgEl.value = '';
                    return;
                }
            }
        });
    }
});
