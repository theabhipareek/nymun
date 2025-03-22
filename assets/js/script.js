document.addEventListener('DOMContentLoaded', function() {
    // Form sections array to track progress
    const sections = [
        'section-welcome',
        'section-personal',
        'section-education',
        'section-mun-experience',
        'section-committee-preference'
    ];

    // Initialize form validation
    const form = document.getElementById('mun-form');
    const formMessages = document.getElementById('form-messages');
    const progressBar = document.getElementById('form-progress');
    const submitBtn = document.getElementById('submit-btn');
    
    // Handle institutional level change
    const institutionalLevel = document.getElementById('institutional-level');
    const schoolDetails = document.getElementById('school-details');
    const collegeDetails = document.getElementById('college-details');
    
    // Check for touch device
    const isTouchDevice = 'ontouchstart' in window || navigator.msMaxTouchPoints > 0;
    
    if (isTouchDevice) {
        // Add touch-friendly classes to buttons and interactive elements
        document.querySelectorAll('.btn').forEach(btn => {
            btn.classList.add('touch-friendly');
        });
        
        document.querySelectorAll('select, input[type="text"], input[type="email"], input[type="tel"], textarea').forEach(input => {
            input.classList.add('touch-friendly-input');
        });
    }
    
    if (institutionalLevel) {
        institutionalLevel.addEventListener('change', function() {
            if (this.value === 'School') {
                schoolDetails.style.display = 'block';
                collegeDetails.style.display = 'none';
                
                // Make school fields required
                document.getElementById('grade').setAttribute('required', '');
                document.getElementById('school-name').setAttribute('required', '');
                
                // Remove required from college fields
                document.getElementById('year').removeAttribute('required');
                document.getElementById('college-name').removeAttribute('required');
            } else if (this.value === 'College') {
                collegeDetails.style.display = 'block';
                schoolDetails.style.display = 'none';
                
                // Make college fields required
                document.getElementById('year').setAttribute('required', '');
                document.getElementById('college-name').setAttribute('required', '');
                
                // Remove required from school fields
                document.getElementById('grade').removeAttribute('required');
                document.getElementById('school-name').removeAttribute('required');
            } else {
                schoolDetails.style.display = 'none';
                collegeDetails.style.display = 'none';
                
                // Remove required from all fields
                document.getElementById('grade').removeAttribute('required');
                document.getElementById('school-name').removeAttribute('required');
                document.getElementById('year').removeAttribute('required');
                document.getElementById('college-name').removeAttribute('required');
            }
        });
    }
    
    // Handle section navigation - next buttons
    document.querySelectorAll('.next-btn').forEach(button => {
        button.addEventListener('click', function() {
            const nextSection = this.getAttribute('data-next');
            const currentSection = this.closest('.form-section').getAttribute('id');
            
            // Find the index of current and next sections
            const currentIndex = sections.indexOf(currentSection);
            const nextIndex = sections.indexOf(nextSection);
            
            // Validate required fields in the current section before proceeding
            if (shouldValidateSection(currentSection)) {
                const currentInputs = document.getElementById(currentSection).querySelectorAll('[required]');
                let isValid = true;
                
                currentInputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('is-invalid');
                        showFormMessage('Please fill in all required fields.', 'danger');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
                
                if (!isValid) {
                    // For mobile, make sure error message is visible without scrolling
                    if (window.innerWidth < 768) {
                        setTimeout(() => {
                            formMessages.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }, 100);
                    }
                    return;
                }
            }
            
            // Hide current section
            document.getElementById(currentSection).style.display = 'none';
            
            // Show next section
            document.getElementById(nextSection).style.display = 'block';
            
            // Update progress bar
            updateProgressBar(nextIndex);
            
            // Scroll to top
            window.scrollTo(0, 0);
            
            // Remove any form messages
            hideFormMessage();
            
            // Add animation to new section
            const elements = document.getElementById(nextSection).querySelectorAll('.animate__animated');
            elements.forEach(el => {
                el.classList.add('animate__fadeInUp');
                // Remove animation class after animation completes
                setTimeout(() => {
                    el.classList.remove('animate__fadeInUp');
                }, 1000);
            });
        });
    });
    
    // Handle section navigation - previous buttons
    document.querySelectorAll('.prev-btn').forEach(button => {
        button.addEventListener('click', function() {
            const prevSection = this.getAttribute('data-prev');
            const currentSection = this.closest('.form-section').getAttribute('id');
            
            // Find the index of current and prev sections
            const currentIndex = sections.indexOf(currentSection);
            const prevIndex = sections.indexOf(prevSection);
            
            // Hide current section
            document.getElementById(currentSection).style.display = 'none';
            
            // Show previous section
            document.getElementById(prevSection).style.display = 'block';
            
            // Update progress bar
            updateProgressBar(prevIndex);
            
            // Scroll to top
            window.scrollTo(0, 0);
            
            // Remove any form messages
            hideFormMessage();
        });
    });
    
    // Form submission handler
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Final validation of all required fields
            const requiredInputs = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                showFormMessage('Please fill in all required fields.', 'danger');
                return;
            }
            
            // Show loading state on button
            submitBtn.disabled = true;
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            
            // Show processing message
            showFormMessage('Processing your registration...', 'info');
            
            // Create a FormData object to handle form data
            const formData = new FormData(form);
            
            // Submit the form using fetch API
            fetch('process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showFormMessage('Registration submitted successfully! Redirecting...', 'success');
                    
                    // Redirect to confirmation page after a slight delay
                    setTimeout(() => {
                        window.location.href = 'confirmation.html';
                    }, 1500);
                } else {
                    showFormMessage('Error: ' + data.message, 'danger');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error submitting form:', error);
                showFormMessage('There was an error submitting your form. Please try again later.', 'danger');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
    
    // Helper function to update progress bar
    function updateProgressBar(sectionIndex) {
        if (progressBar) {
            const progress = ((sectionIndex + 1) / sections.length) * 100;
            progressBar.style.width = progress + '%';
        }
    }
    
    // Helper function to show form messages
    function showFormMessage(message, type) {
        if (formMessages) {
            formMessages.className = 'alert alert-' + type + ' animate__animated animate__fadeIn';
            formMessages.textContent = message;
            formMessages.classList.remove('d-none');
            
            // Scroll to message with better positioning for mobile
            if (window.innerWidth < 768) {
                formMessages.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                formMessages.scrollIntoView({ behavior: 'smooth' });
            }
        }
    }
    
    // Helper function to hide form messages
    function hideFormMessage() {
        if (formMessages) {
            formMessages.classList.add('d-none');
        }
    }
    
    // Helper function to determine if a section should be validated
    function shouldValidateSection(sectionId) {
        // Skip validation for welcome section
        return sectionId !== 'section-welcome';
    }

    // Add window resize handler for responsive adjustments
    window.addEventListener('resize', function() {
        adjustFormForScreenSize();
    });
    
    // Function to adjust form based on screen size
    function adjustFormForScreenSize() {
        const isMobile = window.innerWidth < 768;
        const buttons = document.querySelectorAll('.btn');
        
        buttons.forEach(btn => {
            if (isMobile) {
                btn.classList.add('btn-lg', 'w-100', 'mb-2');
            } else {
                btn.classList.remove('btn-lg', 'w-100', 'mb-2');
            }
        });
        
        // Adjust navigation buttons layout
        document.querySelectorAll('.form-section').forEach(section => {
            const navContainer = section.querySelector('.justify-content-between');
            if (navContainer) {
                if (isMobile) {
                    navContainer.classList.remove('justify-content-between');
                    navContainer.classList.add('flex-column');
                } else {
                    navContainer.classList.add('justify-content-between');
                    navContainer.classList.remove('flex-column');
                }
            }
        });
    }
    
    // Call immediately on load
    adjustFormForScreenSize();

    // Initialize the first section
    document.getElementById('section-welcome').style.display = 'block';
    updateProgressBar(0);
});
