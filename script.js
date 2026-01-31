// Initialize Lucide Icons
lucide.createIcons();

// Slide System State
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const indicatorsContainer = document.getElementById('indicators');
const progressBar = document.getElementById('progressBar');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
let isAnimating = false;

// Create Premium Indicators
slides.forEach((_, index) => {
    const indicator = document.createElement('div');
    indicator.classList.add('indicator');
    if (index === 0) indicator.classList.add('active');
    indicator.addEventListener('click', () => goToSlide(index));
    indicatorsContainer.appendChild(indicator);
});

const indicators = document.querySelectorAll('.indicator');

/**
 * Update Progress Bar and Indicators
 */
function updateProgress() {
    const progress = ((currentSlide + 1) / slides.length) * 100;
    progressBar.style.width = `${progress}%`;

    indicators.forEach((ind, i) => {
        ind.classList.toggle('active', i === currentSlide);
    });
}

/**
 * Main Slide Transition Logic
 */
function goToSlide(index) {
    if (index < 0 || index >= slides.length || isAnimating) return;

    isAnimating = true;

    // Manage Slide Classes for Clean Transitions
    slides.forEach((slide, i) => {
        slide.classList.remove('active', 'prev', 'next');
        if (i === index) {
            slide.classList.add('active');
        } else if (i < index) {
            slide.classList.add('prev');
        } else {
            slide.classList.add('next');
        }
    });

    currentSlide = index;
    updateProgress();

    // Bound Control State
    prevBtn.disabled = currentSlide === 0;
    nextBtn.disabled = currentSlide === slides.length - 1;

    // Transition Guard
    setTimeout(() => {
        isAnimating = false;
    }, 1000); // 1s sync with CSS
}

// Event Listeners for Controls
prevBtn.addEventListener('click', () => goToSlide(currentSlide - 1));
nextBtn.addEventListener('click', () => goToSlide(currentSlide + 1));

// Keyboard Integration (Smooth Presentation Experience)
window.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') goToSlide(currentSlide - 1);
    if (e.key === 'ArrowDown' || e.key === 'ArrowRight' || e.key === 'Space' || e.key === 'Enter') {
        if (e.target.tagName !== 'A') { // Prevent conflict with links
            goToSlide(currentSlide + 1);
        }
    }
});

// Deep Debounced Wheel Support for Presentation Control
let lastWheelTime = 0;
window.addEventListener('wheel', (e) => {
    const now = Date.now();
    if (now - lastWheelTime < 1200) return; // Presentation-style pace

    if (Math.abs(e.deltaY) > 10) { // Filter tiny scrolls
        if (e.deltaY > 0) {
            goToSlide(currentSlide + 1);
        } else {
            goToSlide(currentSlide - 1);
        }
        lastWheelTime = now;
    }
}, { passive: true });

// Initial Load Sequence
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => goToSlide(0), 100);
    initParticles();
});

/**
 * Antigravity Particle System (Dynamic Tech Nodes)
 */
function initParticles() {
    const canvas = document.getElementById('particle-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let particles = [];
    const particleCount = 60;
    let w, h;
    let mouse = { x: null, y: null, radius: 150 };

    function resize() {
        w = canvas.width = window.innerWidth;
        h = canvas.height = window.innerHeight;
    }

    window.addEventListener('resize', resize);
    window.addEventListener('mousemove', (e) => {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
    });

    resize();

    class Particle {
        constructor() {
            this.x = Math.random() * w;
            this.y = Math.random() * h;
            this.size = Math.random() * 2 + 1;
            this.vx = (Math.random() - 0.5) * 0.5;
            this.vy = (Math.random() - 0.5) * 0.5;
        }

        update() {
            this.x += this.vx;
            this.y += this.vy;
            if (this.x < 0 || this.x > w) this.vx *= -1;
            if (this.y < 0 || this.y > h) this.vy *= -1;

            if (mouse.x != null) {
                let dx = mouse.x - this.x;
                let dy = mouse.y - this.y;
                let distance = Math.sqrt(dx * dx + dy * dy);
                if (distance < mouse.radius) {
                    this.x -= dx / 150;
                    this.y -= dy / 150;
                }
            }
        }

        draw() {
            ctx.fillStyle = 'rgba(59, 130, 246, 0.4)';
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function connect() {
        for (let a = 0; a < particles.length; a++) {
            for (let b = a; b < particles.length; b++) {
                let dx = particles[a].x - particles[b].x;
                let dy = particles[a].y - particles[b].y;
                let distance = Math.sqrt(dx * dx + dy * dy);
                if (distance < 150) {
                    let opacity = (1 - distance / 150) * 0.2;
                    ctx.strokeStyle = `rgba(59, 130, 246, ${opacity})`;
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.moveTo(particles[a].x, particles[a].y);
                    ctx.lineTo(particles[b].x, particles[b].y);
                    ctx.stroke();
                }
            }
        }
    }

    function animate() {
        ctx.clearRect(0, 0, w, h);
        particles.forEach(p => {
            p.update();
            p.draw();
        });
        connect();
        requestAnimationFrame(animate);
    }

    for (let i = 0; i < particleCount; i++) particles.push(new Particle());
    animate();
}
