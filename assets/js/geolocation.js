const locateBtn  = document.getElementById('locateBtn');
const cityInput  = document.getElementById('city');
const aiForm     = document.getElementById('aiForm');

/* ── Pulse animation for the locate button ────────────────────── */
const pulseStyle = document.createElement('style');
pulseStyle.textContent = `
    @keyframes locatePulse {
        0%,100% { box-shadow: 0 0 0 0 rgba(0,247,255,0.6); }
        50%      { box-shadow: 0 0 0 8px rgba(0,247,255,0); }
    }
    #locateBtn.locating {
        color: var(--neon-cyan) !important;
        animation: locatePulse 1.2s ease infinite;
    }
`;
document.head.appendChild(pulseStyle);

/* ── Reverse-geocode via Geoapify ─────────────────────────────── */
function reverseGeocode(lat, lng) {
    const url = `https://api.geoapify.com/v1/geocode/reverse?lat=${lat}&lon=${lng}&apiKey=${GEOAPIFY_API_KEY}`;
    return fetch(url)
        .then(r => r.json())
        .then(data => {
            const props = data?.features?.[0]?.properties;
            if (!props) throw new Error('No result');
            // Prefer city > county > state > country
            return props.city || props.county || props.state || props.country || null;
        });
}

/* ── Locate button handler ────────────────────────────────────── */
if (locateBtn) {
    locateBtn.addEventListener('click', () => {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }

        // Visual feedback — start pulsing
        locateBtn.classList.add('locating');
        locateBtn.disabled = true;
        cityInput.placeholder = 'Detecting your location…';

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;

                reverseGeocode(latitude, longitude)
                    .then(cityName => {
                        locateBtn.classList.remove('locating');
                        locateBtn.disabled = false;
                        cityInput.placeholder = 'E.g., Tokyo, Japan or Paris, France...';

                        if (cityName) {
                            cityInput.value = cityName;
                            // Submit the GET form → opens results.php?city=...
                            const form = document.getElementById('aiForm');
                            if (form) form.submit();
                        } else {
                            alert('Could not determine city name from your coordinates.');
                        }
                    })
                    .catch(err => {
                        locateBtn.classList.remove('locating');
                        locateBtn.disabled = false;
                        cityInput.placeholder = 'E.g., Tokyo, Japan or Paris, France...';
                        console.error('Reverse geocode error:', err);
                        alert('Could not identify your location. Please type it manually.');
                    });
            },
            (error) => {
                locateBtn.classList.remove('locating');
                locateBtn.disabled = false;
                cityInput.placeholder = 'E.g., Tokyo, Japan or Paris, France...';

                const messages = {
                    1: 'Location access denied. Please allow location access in your browser settings.',
                    2: 'Position unavailable. Try again or enter city manually.',
                    3: 'Location request timed out. Try again.',
                };
                alert(messages[error.code] || 'Location error: ' + error.message);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    });
}
