// Fault report client-side helper with live event validation
document.addEventListener('DOMContentLoaded', function () {
    var form        = document.getElementById('reportForm');
    var computerEl  = document.getElementById('computer_id');
    var componentEl = document.getElementById('component');
    var titleEl     = document.getElementById('issue_title');
    var description = document.getElementById('description');
    var counter     = document.getElementById('reportCounter');
    var imageInput  = document.getElementById('report_image') || document.getElementById('image');

    // Create inline error helper
    function getOrCreateError(el) {
        var next = el.nextElementSibling;
        if (next && next.classList.contains('input-error-hint')) {
            return next;
        }
        var span = document.createElement('small');
        span.className = 'input-error-hint';
        span.style.cssText = 'color: #dc2626; display: none; font-size: 11.5px; font-weight: 500; margin-top: 4px;';
        el.parentNode.insertBefore(span, el.nextSibling);
        return span;
    }

    function setValidationState(el, isValid, errorMsg) {
        var errSpan = getOrCreateError(el);
        if (isValid) {
            el.style.borderColor = '#16a34a';
            errSpan.style.display = 'none';
        } else {
            el.style.borderColor = '#dc2626';
            errSpan.textContent = errorMsg;
            errSpan.style.display = 'block';
        }
        return isValid;
    }

    function validateComputer() {
        if (!computerEl) return true;
        var valid = computerEl.value !== '';
        return setValidationState(computerEl, valid, 'Please choose a computer.');
    }

    function validateComponent() {
        if (!componentEl) return true;
        var valid = componentEl.value !== '';
        return setValidationState(componentEl, valid, 'Please select the faulty component.');
    }

    function validateDescription() {
        if (!description) return true;
        var len = description.value.trim().length;
        var valid = len >= 10;
        return setValidationState(description, valid, len === 0 ? 'Description is required.' : 'Please enter at least 10 characters.');
    }

    // Live events
    if (computerEl) {
        computerEl.addEventListener('change', validateComputer);
    }

    if (componentEl) {
        componentEl.addEventListener('change', function () {
            validateComponent();
            // Auto-fill title if empty
            if (titleEl && titleEl.value.trim() === '' && componentEl.selectedIndex > 0) {
                var compText = componentEl.options[componentEl.selectedIndex].text;
                titleEl.value = compText + ' Issue';
            }
        });
    }

    // Update character counter for description with live feedback
    if (description && counter) {
        function updateCounter() {
            var len = description.value.length;
            counter.textContent = len + '/500';
            if (len < 10) {
                counter.style.color = '#dc2626';
            } else {
                counter.style.color = 'var(--muted, #64748b)';
            }
        }
        description.addEventListener('input', function () {
            updateCounter();
            validateDescription();
        });
        description.addEventListener('blur', validateDescription);
        updateCounter();
    }

    // Validate image file size (max 5MB) and type
    if (imageInput) {
        imageInput.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;

            var allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            if (allowed.indexOf(file.type) === -1) {
                alert('Invalid file format. Please upload a JPG, PNG, or WebP photo.');
                this.value = '';
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('Image size exceeds 5MB limit. Please choose a smaller file.');
                this.value = '';
            }
        });
    }

    // Prevent double form submission & enforce full validation
    if (form) {
        form.addEventListener('submit', function (e) {
            var vComp  = validateComputer();
            var vPart  = validateComponent();
            var vDesc  = validateDescription();

            if (!vComp || !vPart || !vDesc) {
                e.preventDefault();
                if (!vComp && computerEl) computerEl.focus();
                else if (!vPart && componentEl) componentEl.focus();
                else if (!vDesc && description) description.focus();
                return;
            }

            var submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting Report...';
            }
        });
    }
});
