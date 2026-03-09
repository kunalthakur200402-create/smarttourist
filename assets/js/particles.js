/**
 * particles.js — animated glowing dots + connection lines background
 * New theme: Deep Amethyst + Amber Gold
 */
(function () {
    const canvas = document.createElement('canvas');
    canvas.id = 'particles-bg';
    canvas.style.cssText = [
        'position:fixed', 'top:0', 'left:0',
        'width:100vw', 'height:100vh',
        'z-index:0', 'pointer-events:none', 'opacity:0.75'
    ].join(';');
    document.body.prepend(canvas);

    const ctx = canvas.getContext('2d');

    // Amethyst + Gold colour palette for dots
    const COLORS = ['#a78bfa', '#f59e0b', '#34d399', '#f472b6', '#c4b5fd'];

    let W, H, dots;

    function resize() {
        W = canvas.width  = window.innerWidth;
        H = canvas.height = window.innerHeight;
    }

    function initDots() {
        const count = Math.min(Math.floor((W * H) / 14000), 100); // density-aware
        dots = Array.from({ length: count }, () => ({
            x:     Math.random() * W,
            y:     Math.random() * H,
            r:     Math.random() * 2 + 0.6,
            vx:    (Math.random() - 0.5) * 0.25,
            vy:    (Math.random() - 0.5) * 0.25,
            color: COLORS[Math.floor(Math.random() * COLORS.length)],
            alpha: Math.random() * 0.5 + 0.25,
        }));
    }

    function hexToRgb(hex) {
        const r = parseInt(hex.slice(1,3),16);
        const g = parseInt(hex.slice(3,5),16);
        const b = parseInt(hex.slice(5,7),16);
        return `${r},${g},${b}`;
    }

    let raf;
    function draw() {
        ctx.clearRect(0, 0, W, H);

        // Connection lines between nearby dots
        for (let i = 0; i < dots.length; i++) {
            for (let j = i + 1; j < dots.length; j++) {
                const dx   = dots[i].x - dots[j].x;
                const dy   = dots[i].y - dots[j].y;
                const dist = Math.hypot(dx, dy);
                if (dist < 130) {
                    ctx.beginPath();
                    ctx.moveTo(dots[i].x, dots[i].y);
                    ctx.lineTo(dots[j].x, dots[j].y);
                    ctx.strokeStyle = `rgba(167,139,250,${0.18 * (1 - dist / 130)})`;
                    ctx.lineWidth = 0.6;
                    ctx.stroke();
                }
            }
        }

        // Dots + soft glow halo
        dots.forEach(d => {
            const rgb = hexToRgb(d.color);

            // Glow halo
            const glow = ctx.createRadialGradient(d.x, d.y, 0, d.x, d.y, d.r * 5);
            glow.addColorStop(0, `rgba(${rgb},${d.alpha * 0.45})`);
            glow.addColorStop(1, 'transparent');
            ctx.beginPath();
            ctx.arc(d.x, d.y, d.r * 5, 0, Math.PI * 2);
            ctx.fillStyle = glow;
            ctx.fill();

            // Core dot
            ctx.beginPath();
            ctx.arc(d.x, d.y, d.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(${rgb},${d.alpha})`;
            ctx.fill();

            // Move
            d.x += d.vx;
            d.y += d.vy;
            if (d.x < 0)  d.x = W;
            if (d.x > W)  d.x = 0;
            if (d.y < 0)  d.y = H;
            if (d.y > H)  d.y = 0;
        });

        raf = requestAnimationFrame(draw);
    }

    resize();
    initDots();
    draw();

    window.addEventListener('resize', () => {
        cancelAnimationFrame(raf);
        resize();
        initDots();
        draw();
    });
})();
