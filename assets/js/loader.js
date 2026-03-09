document.addEventListener('DOMContentLoaded', () => {
    const loader = document.getElementById('ai-preloader');
    const heroContent = document.querySelector('.hero-content');
    const canvasContainer = document.getElementById('canvas-container');
    const dashboardContainer = document.querySelector('.container');

    // Simulate AI Initialization (Extended for Neural Pulse)
    setTimeout(() => {
        if (loader) {
            loader.style.opacity = '0';
            loader.style.pointerEvents = 'none';
            
            // Trigger Fade-ins
            if (heroContent) heroContent.style.opacity = '1';
            if (canvasContainer) canvasContainer.style.opacity = '0.6';
            if (dashboardContainer) dashboardContainer.style.opacity = '1';

            setTimeout(() => {
                loader.remove();
            }, 1000);
        }
    }, 2500);

    // Adventure Button Animation
    const adventureBtn = document.querySelector('.btn-primary');
    if (adventureBtn) {
        // Create warp overlay if not exists
        let warpOverlay = document.querySelector('.warp-overlay');
        if (!warpOverlay) {
            warpOverlay = document.createElement('div');
            warpOverlay.className = 'warp-overlay';
            document.body.appendChild(warpOverlay);
        }

        adventureBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const targetUrl = adventureBtn.getAttribute('href');
            
            document.body.classList.add('start-warp');
            
            // Redirect after animation
            setTimeout(() => {
                window.location.href = targetUrl;
            }, 750);
        });
    }
});
