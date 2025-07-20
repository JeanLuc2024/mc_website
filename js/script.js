/**
 * Main JavaScript file for Byiringiro Valentin MC Website
 * Author: AI Assistant
 * Date: May 25, 2025
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initNavigation();
    initTestimonialSlider();
    initGalleryFilter();
    initLightbox();
    initFaqAccordion();
    initFormValidation();
    initScrollEffects();
    initAjaxForms();
});

/**
 * Mobile Navigation Toggle
 */
function initNavigation() {
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');
    
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navLinks.classList.toggle('active');
            
            // Toggle hamburger animation
            const bars = hamburger.querySelectorAll('.bar');
            if (hamburger.classList.contains('active')) {
                bars[0].style.transform = 'rotate(-45deg) translate(-5px, 6px)';
                bars[1].style.opacity = '0';
                bars[2].style.transform = 'rotate(45deg) translate(-5px, -6px)';
            } else {
                bars[0].style.transform = 'none';
                bars[1].style.opacity = '1';
                bars[2].style.transform = 'none';
            }
        });
        
        // Close mobile menu when clicking on a nav link
        const navItems = document.querySelectorAll('.nav-links a');
        navItems.forEach(item => {
            item.addEventListener('click', function() {
                if (navLinks.classList.contains('active')) {
                    hamburger.click();
                }
            });
        });
    }
    
    // Header scroll effect
    const header = document.querySelector('header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
}

/**
 * Testimonial Slider
 */
function initTestimonialSlider() {
    const slider = document.querySelector('.testimonial-slider');
    if (!slider) return;
    
    const slides = slider.querySelectorAll('.testimonial-slide');
    const prevBtn = document.querySelector('.prev-slide');
    const nextBtn = document.querySelector('.next-slide');
    
    if (slides.length <= 1) return;
    
    let currentSlide = 0;
    
    // Hide all slides except the first one
    for (let i = 1; i < slides.length; i++) {
        slides[i].style.display = 'none';
    }
    
    // Function to show a specific slide
    function showSlide(index) {
        // Hide all slides
        slides.forEach(slide => {
            slide.style.display = 'none';
            slide.style.opacity = '0';
        });
        
        // Show the current slide with fade effect
        slides[index].style.display = 'block';
        setTimeout(() => {
            slides[index].style.opacity = '1';
        }, 10);
    }
    
    // Next slide function
    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }
    
    // Previous slide function
    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
    }
    
    // Event listeners for next and previous buttons
    if (nextBtn) {
        nextBtn.addEventListener('click', nextSlide);
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', prevSlide);
    }
    
    // Auto slide every 5 seconds
    let slideInterval = setInterval(nextSlide, 5000);
    
    // Pause auto slide on hover
    slider.addEventListener('mouseenter', () => {
        clearInterval(slideInterval);
    });
    
    slider.addEventListener('mouseleave', () => {
        slideInterval = setInterval(nextSlide, 5000);
    });
    
    // Add transition effect to slides
    slides.forEach(slide => {
        slide.style.transition = 'opacity 0.5s ease';
    });
}

/**
 * Gallery Filter
 */
function initGalleryFilter() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    if (!filterBtns.length || !galleryItems.length) return;
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            filterBtns.forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to clicked button
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            
            // Filter gallery items
            galleryItems.forEach(item => {
                if (filter === 'all' || item.classList.contains(filter)) {
                    item.style.display = 'block';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    }, 10);
                } else {
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 500);
                }
            });
        });
    });
    
    // Add transition effect to gallery items
    galleryItems.forEach(item => {
        item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    });
}

/**
 * Lightbox for Gallery
 */
function initLightbox() {
    const galleryZoomBtns = document.querySelectorAll('.gallery-zoom');
    const lightbox = document.querySelector('.lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxCaption = document.querySelector('.lightbox-caption');
    const closeLightbox = document.querySelector('.close-lightbox');
    
    if (!galleryZoomBtns.length || !lightbox || !lightboxImg) return;
    
    galleryZoomBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const imgSrc = this.getAttribute('href');
            const imgTitle = this.closest('.overlay-content').querySelector('h3').textContent;
            const imgDesc = this.closest('.overlay-content').querySelector('p').textContent;
            
            lightboxImg.src = imgSrc;
            lightboxCaption.textContent = `${imgTitle} - ${imgDesc}`;
            lightbox.style.display = 'block';
            
            // Prevent body scrolling when lightbox is open
            document.body.style.overflow = 'hidden';
        });
    });
    
    // Close lightbox when clicking on close button
    if (closeLightbox) {
        closeLightbox.addEventListener('click', function() {
            lightbox.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
    }
    
    // Close lightbox when clicking outside the image
    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox) {
            lightbox.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
    
    // Close lightbox when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && lightbox.style.display === 'block') {
            lightbox.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
}

/**
 * FAQ Accordion
 */
function initFaqAccordion() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    if (!faqItems.length) return;
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');
        
        question.addEventListener('click', function() {
            // Check if this item is already active
            const isActive = item.classList.contains('active');
            
            // Close all FAQ items
            faqItems.forEach(faqItem => {
                faqItem.classList.remove('active');
                const faqAnswer = faqItem.querySelector('.faq-answer');
                faqAnswer.style.maxHeight = null;
            });
            
            // If the clicked item wasn't active, open it
            if (!isActive) {
                item.classList.add('active');
                answer.style.maxHeight = answer.scrollHeight + 'px';
            }
        });
    });
}

/**
 * Form Validation
 */
function initFormValidation() {
    const bookingForm = document.getElementById('appointmentForm');
    const contactForm = document.getElementById('contactForm');
    
    // Booking Form Validation
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Get form fields
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');
            const eventDate = document.getElementById('event_date');
            const eventTime = document.getElementById('event_time');
            const eventType = document.getElementById('event_type');
            const eventLocation = document.getElementById('event_location');
            const guests = document.getElementById('guests');
            const terms = document.getElementById('terms');
            
            // Reset error messages
            const errorElements = bookingForm.querySelectorAll('.error-message');
            errorElements.forEach(element => {
                element.remove();
            });
            
            // Validate Name
            if (!name.value.trim()) {
                showError(name, 'Please enter your full name');
                isValid = false;
            }
            
            // Validate Email
            if (!validateEmail(email.value)) {
                showError(email, 'Please enter a valid email address');
                isValid = false;
            }
            
            // Validate Phone
            if (!phone.value.trim()) {
                showError(phone, 'Please enter your phone number');
                isValid = false;
            }
            
            // Validate Event Date
            if (!eventDate.value) {
                showError(eventDate, 'Please select an event date');
                isValid = false;
            } else {
                const selectedDate = new Date(eventDate.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (selectedDate < today) {
                    showError(eventDate, 'Event date cannot be in the past');
                    isValid = false;
                }
            }
            
            // Validate Event Time
            if (!eventTime.value) {
                showError(eventTime, 'Please select an event time');
                isValid = false;
            }
            
            // Validate Event Type
            if (!eventType.value) {
                showError(eventType, 'Please select an event type');
                isValid = false;
            }
            
            // Validate Event Location
            if (!eventLocation.value.trim()) {
                showError(eventLocation, 'Please enter the event location');
                isValid = false;
            }
            
            // Validate Guests
            if (!guests.value || guests.value < 1) {
                showError(guests, 'Please enter the number of guests');
                isValid = false;
            }
            
            // Validate Terms
            if (!terms.checked) {
                showError(terms, 'You must agree to the terms and conditions');
                isValid = false;
            }
            
            // Prevent form submission if validation fails
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // Contact Form Validation
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Get form fields
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            const subject = document.getElementById('subject');
            const message = document.getElementById('message');
            
            // Reset error messages
            const errorElements = contactForm.querySelectorAll('.error-message');
            errorElements.forEach(element => {
                element.remove();
            });
            
            // Validate Name
            if (!name.value.trim()) {
                showError(name, 'Please enter your full name');
                isValid = false;
            }
            
            // Validate Email
            if (!validateEmail(email.value)) {
                showError(email, 'Please enter a valid email address');
                isValid = false;
            }
            
            // Validate Subject
            if (!subject.value.trim()) {
                showError(subject, 'Please enter a subject');
                isValid = false;
            }
            
            // Validate Message
            if (!message.value.trim()) {
                showError(message, 'Please enter your message');
                isValid = false;
            }
            
            // Prevent form submission if validation fails
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
}

/**
 * AJAX Form Submission
 */
function initAjaxForms() {
    const bookingForm = document.getElementById('appointmentForm');
    const contactForm = document.getElementById('contactForm');
    
    // Booking Form AJAX Submission - REMOVED
    // This was causing duplicate submissions. The AJAX handler is now in booking.html
    // Only keeping the validation handler above

    
    // Contact Form AJAX Submission
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            // Check if form is valid (validation is handled in initFormValidation)
            const errorElements = contactForm.querySelectorAll('.error-message');
            if (errorElements.length > 0) {
                return; // Don't submit if there are validation errors
            }
            
            // Create form data object
            const formData = new FormData(contactForm);
            
            // Show loading state
            const submitBtn = contactForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.textContent;
            submitBtn.textContent = 'Sending...';
            submitBtn.disabled = true;
            
            // Create response message container if it doesn't exist
            let responseContainer = document.getElementById('contact-response');
            if (!responseContainer) {
                responseContainer = document.createElement('div');
                responseContainer.id = 'contact-response';
                responseContainer.className = 'response-message';
                responseContainer.style.padding = '15px';
                responseContainer.style.marginTop = '20px';
                responseContainer.style.borderRadius = 'var(--border-radius-md)';
                responseContainer.style.display = 'none';
                contactForm.appendChild(responseContainer);
            }
            
            // Send AJAX request
            fetch(contactForm.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Reset loading state
                submitBtn.textContent = originalBtnText;
                submitBtn.disabled = false;
                
                // Display response message
                if (data.success) {
                    // Success message
                    responseContainer.textContent = data.message;
                    responseContainer.style.backgroundColor = 'rgba(46, 204, 113, 0.1)';
                    responseContainer.style.color = 'var(--success-color)';
                    responseContainer.style.border = '1px solid var(--success-color)';
                    responseContainer.style.display = 'block';
                    
                    // Reset form
                    contactForm.reset();
                    
                    // Scroll to response message
                    responseContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    // Error message
                    responseContainer.textContent = data.message;
                    responseContainer.style.backgroundColor = 'rgba(231, 76, 60, 0.1)';
                    responseContainer.style.color = 'var(--danger-color)';
                    responseContainer.style.border = '1px solid var(--danger-color)';
                    responseContainer.style.display = 'block';
                    
                    // Scroll to response message
                    responseContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                // Hide response message after 10 seconds if it's a success message
                if (data.success) {
                    setTimeout(() => {
                        responseContainer.style.display = 'none';
                    }, 10000);
                }
            })
            .catch(error => {
                // Reset loading state
                submitBtn.textContent = originalBtnText;
                submitBtn.disabled = false;
                
                // Display error message
                responseContainer.textContent = 'An error occurred. Please try again later.';
                responseContainer.style.backgroundColor = 'rgba(231, 76, 60, 0.1)';
                responseContainer.style.color = 'var(--danger-color)';
                responseContainer.style.border = '1px solid var(--danger-color)';
                responseContainer.style.display = 'block';
                
                console.error('Error:', error);
            });
        });
    }
}

/**
 * Helper function to show error message
 */
function showError(input, message) {
    const formGroup = input.closest('.form-group');
    const errorElement = document.createElement('div');
    errorElement.className = 'error-message';
    errorElement.textContent = message;
    errorElement.style.color = 'var(--danger-color)';
    errorElement.style.fontSize = '1.4rem';
    errorElement.style.marginTop = '0.5rem';
    formGroup.appendChild(errorElement);
    
    // Highlight the input field
    input.style.borderColor = 'var(--danger-color)';
    
    // Remove error when input changes
    input.addEventListener('input', function() {
        errorElement.remove();
        input.style.borderColor = '';
    });
}

/**
 * Helper function to validate email
 */
function validateEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

/**
 * Scroll Effects
 */
function initScrollEffects() {
    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]:not([href="#"])');
    
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const headerHeight = document.querySelector('header').offsetHeight;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Scroll to top button
    const scrollTopBtn = document.createElement('button');
    scrollTopBtn.className = 'scroll-top-btn';
    scrollTopBtn.innerHTML = '<i class="fas fa-chevron-up"></i>';
    scrollTopBtn.style.position = 'fixed';
    scrollTopBtn.style.bottom = '20px';
    scrollTopBtn.style.right = '20px';
    scrollTopBtn.style.width = '40px';
    scrollTopBtn.style.height = '40px';
    scrollTopBtn.style.borderRadius = '50%';
    scrollTopBtn.style.backgroundColor = 'var(--primary-color)';
    scrollTopBtn.style.color = 'var(--white)';
    scrollTopBtn.style.border = 'none';
    scrollTopBtn.style.display = 'none';
    scrollTopBtn.style.alignItems = 'center';
    scrollTopBtn.style.justifyContent = 'center';
    scrollTopBtn.style.cursor = 'pointer';
    scrollTopBtn.style.zIndex = '99';
    scrollTopBtn.style.boxShadow = 'var(--shadow-md)';
    
    document.body.appendChild(scrollTopBtn);
    
    scrollTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollTopBtn.style.display = 'flex';
            scrollTopBtn.style.opacity = '1';
        } else {
            scrollTopBtn.style.opacity = '0';
            setTimeout(() => {
                if (window.pageYOffset <= 300) {
                    scrollTopBtn.style.display = 'none';
                }
            }, 300);
        }
    });
    
    // Animate elements on scroll
    const animateElements = document.querySelectorAll('.service-card, .feature-card, .pricing-card, .gallery-item, .step, .info-item');
    
    if (animateElements.length) {
        // Add initial styles
        animateElements.forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        });
        
        // Check if element is in viewport
        function isInViewport(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.8 &&
                rect.bottom >= 0
            );
        }
        
        // Animate elements when they come into view
        function animateOnScroll() {
            animateElements.forEach(element => {
                if (isInViewport(element)) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        }
        
        // Run on load
        animateOnScroll();
        
        // Run on scroll
        window.addEventListener('scroll', animateOnScroll);
    }
}
