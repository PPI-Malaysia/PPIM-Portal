document.addEventListener('DOMContentLoaded', function () {
    const navLinks = document.querySelectorAll('.table-navigation a');
    const sections = document.querySelectorAll('.table-section');

    // Show section by ID
    function showSection(targetId) {
        sections.forEach(section => section.classList.remove('active'));
        navLinks.forEach(link => link.classList.remove('active'));

        const targetSection = document.getElementById(targetId);
        if (targetSection) targetSection.classList.add('active');

        const activeLink = document.querySelector(`a[href="#${targetId}"]`);
        if (activeLink) activeLink.classList.add('active');
    }

    // Handle nav link click
    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            showSection(targetId);

            history.pushState(null, null, `#${targetId}`);
            document.getElementById(targetId)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Check hash on load or change
    function checkHash() {
        const hash = window.location.hash.substring(1);
        if (hash && document.getElementById(hash)) {
            showSection(hash);
        } else {
            showSection('university_type'); // default section
        }
    }

    window.addEventListener('hashchange', checkHash);
    checkHash(); // on load

    // Form submit button feedback
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function () {
            const button = this.querySelector('button[type="submit"]');
            if (button) {
                const originalText = button.textContent;
                button.disabled = true;
                button.textContent = 'Processing...';
                setTimeout(() => {
                    button.disabled = false;
                    button.textContent = originalText;
                }, 3000);
            }
        });

        // Form delete confirmation
        const isDeleteForm = form.querySelector('input[name="action"][value="delete"]');
        if (isDeleteForm) {
            form.addEventListener('submit', function (e) {
                const confirmed = confirm('Are you sure you want to delete this record? This action cannot be undone.');
                if (!confirmed) e.preventDefault();
            });
        }
    });

    // Email field validation
    document.querySelectorAll('input[type="email"]').forEach(input => {
        input.addEventListener('blur', function () {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            this.setCustomValidity(this.value && !emailRegex.test(this.value) ? 'Please enter a valid email address' : '');
        });
    });

    // Phone field validation
    document.querySelectorAll('input[type="tel"]').forEach(input => {
        input.addEventListener('blur', function () {
            const phoneRegex = /^[\+]?[0-9\s\-\(\)]{7,15}$/;
            this.setCustomValidity(this.value && !phoneRegex.test(this.value) ? 'Please enter a valid phone number' : '');
        });
    });

    // Enhanced delete buttons with confirmation (and remove inline onclick)
    document.querySelectorAll('button[onclick*="confirm"]').forEach(button => {
        button.removeAttribute('onclick');
        button.addEventListener('click', function (e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
                this.closest('form')?.submit();
            }
        });
    });

    // Auto-hide alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});