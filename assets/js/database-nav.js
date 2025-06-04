// Navigation and Section Management
document.addEventListener('DOMContentLoaded', function() {
    // Get all navigation links and sections
    const navLinks = document.querySelectorAll('.table-navigation a');
    const sections = document.querySelectorAll('.table-section');
    
    // Function to show a specific section
    function showSection(targetId) {
        // Hide all sections
        sections.forEach(section => {
            section.classList.remove('active');
        });
        
        // Remove active class from all nav links
        navLinks.forEach(link => {
            link.classList.remove('active');
        });
        
        // Show target section
        const targetSection = document.getElementById(targetId);
        if (targetSection) {
            targetSection.classList.add('active');
        }
        
        // Add active class to clicked nav link
        const activeLink = document.querySelector(`a[href="#${targetId}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    }
    
    // Add click event listeners to navigation links
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            showSection(targetId);
            
            // Update URL hash without jumping
            history.pushState(null, null, `#${targetId}`);
        });
    });
    
    // Check URL hash on page load
    function checkHash() {
        const hash = window.location.hash.substring(1);
        if (hash && document.getElementById(hash)) {
            showSection(hash);
        } else {
            // Show first section by default
            showSection('university_type');
        }
    }
    
    // Handle browser back/forward buttons
    window.addEventListener('hashchange', checkHash);
    
    // Initial load
    checkHash();
    
    // Form submission feedback
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const button = this.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = true;
                button.textContent = 'Processing...';
                
                // Re-enable after 3 seconds (fallback)
                setTimeout(() => {
                    button.disabled = false;
                    button.textContent = button.textContent.replace('Processing...', 
                        button.textContent.includes('Update') ? 'Update' : 
                        button.textContent.includes('Delete') ? 'Delete' : 'Add');
                }, 3000);
            }
        });
    });
    
    // Confirm delete with better styling
    const deleteButtons = document.querySelectorAll('button[onclick*="confirm"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const confirmed = confirm('Are you sure you want to delete this record? This action cannot be undone.');
            if (confirmed) {
                this.closest('form').submit();
            }
        });
        // Remove the inline onclick
        button.removeAttribute('onclick');
    });
});