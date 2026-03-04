let currentSlide = 0;
function initSlider() {
    const slides = document.querySelectorAll('.slide');
    if (slides.length === 0) return;

    function showSlide(n) {
        slides[currentSlide].classList.remove('active');
        currentSlide = (n + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
    }

    setInterval(() => {
        showSlide(currentSlide + 1);
    }, 5000);
}

document.addEventListener('DOMContentLoaded', () => {
    initSlider();
    
    // Theme Toggle
    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;
    const savedTheme = localStorage.getItem('theme') || 'light';
    body.classList.add(savedTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            if (body.classList.contains('light')) {
                body.classList.replace('light', 'dark');
                localStorage.setItem('theme', 'dark');
            } else {
                body.classList.replace('dark', 'light');
                localStorage.setItem('theme', 'light');
            }
        });
    }

    // --- Mobile Menu Toggle ---
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const navLinks = document.getElementById('nav-links');

    if (menuToggle && navLinks) {
        console.log('Mobile menu system initialized');
        
        function toggleMenu(e) {
            if (e) e.stopPropagation();
            const isOpen = navLinks.classList.toggle('active');
            menuToggle.classList.toggle('open');
            console.log('Menu toggled. New state: ' + (isOpen ? 'OPEN' : 'CLOSED'));
            
            const spans = menuToggle.querySelectorAll('span');
            if (isOpen) {
                spans[0].style.transform = 'rotate(45deg) translate(5px, 6px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(5px, -6px)';
                document.body.style.overflow = 'hidden';
            } else {
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        menuToggle.addEventListener('click', toggleMenu);

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (navLinks.classList.contains('active') && !navLinks.contains(e.target) && !menuToggle.contains(e.target)) {
                console.log('Closing menu: Clicked outside');
                toggleMenu();
            }
        });

        // Close menu when clicking any link
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                console.log('Closing menu: Link clicked');
                if (navLinks.classList.contains('active')) {
                    toggleMenu();
                }
            });
        });
    } else {
        console.error('Mobile menu elements not found! ID check: toggle=' + !!menuToggle + ', links=' + !!navLinks);
    }
});

function startAdTimer(duration, onTick, onComplete) {
    let timeLeft = duration;
    const timerDisplay = document.getElementById('ad-timer');
    
    // Support two-argument call for backward compatibility
    if (typeof onTick === 'function' && typeof onComplete === 'undefined') {
        onComplete = onTick;
        onTick = null;
    }
    
    // Initial tick to set starting state
    if (onTick) onTick(timeLeft);

    const interval = setInterval(() => {
        timeLeft--;
        if (timerDisplay) timerDisplay.textContent = Math.max(0, timeLeft);
        
        if (onTick) onTick(timeLeft);
        
        if (timeLeft <= 0) {
            clearInterval(interval);
            if (timerDisplay) timerDisplay.textContent = "0";
            if (onComplete) onComplete();
        }
    }, 1000);
}
