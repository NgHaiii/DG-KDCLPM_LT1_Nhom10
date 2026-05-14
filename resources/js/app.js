document.addEventListener('DOMContentLoaded', () => {
    // Smooth scroll for links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.card-hover, [class*="animate-"]').forEach(el => {
        observer.observe(el);
    });

    // Dark mode toggle
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
    const toggleDarkMode = () => {
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
    };

    // Apply saved preference
    if (localStorage.getItem('darkMode') === 'true') {
        document.documentElement.classList.add('dark');
    } else if (localStorage.getItem('darkMode') === 'false') {
        document.documentElement.classList.remove('dark');
    }

    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('shadow-lg');
        } else {
            navbar.classList.remove('shadow-lg');
        }
    });
});

// Export for module systems
export {};