const dots = [];
const dotCount = 12;

// Create dots
for (let i = 0; i < dotCount; i++) {
    const dot = document.createElement('div');
    dot.className = 'cursor-dot';
    document.body.appendChild(dot);
    dots.push({
        el: dot,
        x: 0,
        y: 0,
        targetX: 0,
        targetY: 0,
        speed: 0.1 + (i * 0.02) // Each dot has a slightly different speed for trailing
    });
}

document.addEventListener('mousemove', (e) => {
    dots.forEach(dot => {
        dot.targetX = e.clientX;
        dot.targetY = e.clientY;
    });
});

function animateDots() {
    dots.forEach((dot, index) => {
        dot.x += (dot.targetX - dot.x) * dot.speed;
        dot.y += (dot.targetY - dot.y) * dot.speed;
        
        dot.el.style.transform = `translate(${dot.x}px, ${dot.y}px) scale(${1 - index/dotCount})`;
    });
    requestAnimationFrame(animateDots);
}

// Initial positioning
document.addEventListener('mouseenter', () => {
    dots.forEach(dot => dot.el.style.opacity = '1');
});
document.addEventListener('mouseleave', () => {
    dots.forEach(dot => dot.el.style.opacity = '0');
});

animateDots();
