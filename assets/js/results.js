/* ================================================================
   results.js  — powers results.php
   Fetches Geoapify data and renders the map + tab content.
================================================================ */

const resultDiv  = document.getElementById('result');
const panelTabs  = document.getElementById('panelTabs');
const mapLoader  = document.getElementById('map-loader');
const aiLoader   = document.getElementById('ai-loader');
const mapLegend  = document.getElementById('mapLegend');

let map;
let markersLayer;
let currentData = null;

/* ── Leaflet map initialisation ─────────────────────────────── */
function initMap(lat, lng) {
    const pos = [parseFloat(lat), parseFloat(lng)];

    if (!map) {
        map = L.map('map', { zoomControl: true }).setView(pos, 13);
        L.tileLayer(
            `https://maps.geoapify.com/v1/tile/dark-matter-yellow-roads/{z}/{x}/{y}.png?apiKey=${GEOAPIFY_API_KEY}`,
            {
                maxZoom: 20,
                attribution: '&copy; <a href="https://www.geoapify.com/">Geoapify</a> &copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> contributors'
            }
        ).addTo(map);
        markersLayer = L.layerGroup().addTo(map);
    } else {
        map.setView(pos, 13);
        markersLayer.clearLayers();
    }

    // City centre marker (glowing cyan dot)
    L.marker(pos, {
        icon: L.divIcon({
            className: '',
            html: `<div style="
                width:16px;height:16px;
                background:#00f7ff;
                border:3px solid #fff;
                border-radius:50%;
                box-shadow:0 0 14px #00f7ff,0 0 28px #00f7ff;
            "></div>`,
            iconSize: [16,16], iconAnchor: [8,8]
        })
    }).addTo(markersLayer);
}

/* ── Coloured dot icon factory ──────────────────────────────── */
function makeIcon(color) {
    return L.divIcon({
        className: '',
        html: `<div style="
            width:10px;height:10px;
            background:${color};
            border:2px solid rgba(255,255,255,0.55);
            border-radius:50%;
            box-shadow:0 0 6px ${color};
        "></div>`,
        iconSize: [10,10], iconAnchor: [5,5]
    });
}

/* ── Markdown parser ─────────────────────────────────────────── */
const parseMarkdown = (text) => {
    if (!text) return '';
    if (typeof marked?.parse === 'function') return marked.parse(text);
    if (typeof marked === 'function')        return marked(text);
    return text;
};

/* ── Render list cards ──────────────────────────────────────── */
function renderList(items, title, metaKey) {
    if (!items || !items.length) return `
        <div style="text-align:center;padding:50px 20px;color:var(--text-muted);">
            No data available for this category.
        </div>`;
    let html = `
        <div style="color:var(--neon-cyan);font-size:0.8rem;text-transform:uppercase;
                    font-weight:800;margin-bottom:22px;display:flex;align-items:center;gap:10px;">
            <span style="width:22px;height:2px;background:var(--neon-cyan);"></span>
            ${title}
        </div>
        <div style="display:grid;grid-template-columns:1fr;gap:16px;">`;
    items.forEach(item => {
        html += `
            <div style="background:rgba(255,255,255,0.03);border:1px solid var(--glass-border);
                        border-radius:18px;padding:20px;transition:transform 0.3s ease;"
                 onmouseover="this.style.transform='translateY(-2px)'"
                 onmouseout="this.style.transform='translateY(0)'">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:7px;">
                    <div style="font-weight:800;color:#fff;font-size:1rem;">${item.name}</div>
                    <div style="background:rgba(0,247,255,0.1);color:var(--neon-cyan);
                                padding:3px 9px;border-radius:8px;font-size:0.68rem;
                                font-weight:800;text-transform:uppercase;letter-spacing:0.5px;white-space:nowrap;margin-left:10px;">
                        ${item[metaKey] || ''}
                    </div>
                </div>
                <div style="color:var(--text-muted);font-size:0.92rem;line-height:1.6;">
                    ${item.desc || ''}
                </div>
            </div>`;
    });
    html += `</div>`;
    return html;
}

/* ── Tab renderer ────────────────────────────────────────────── */
function renderTab(tab) {
    if (!currentData) return;
    let html = '';
    switch (tab) {
        case 'insights':
            html = `<div>${parseMarkdown(currentData.guide_markdown || 'No insights available.')}</div>`;
            break;
        case 'stay':
            html = renderList(currentData.hotels, '🏨 Recommended Stays', 'price_range');
            break;
        case 'dining':
            html = renderList(currentData.restaurants, '🍽️ Exceptional Dining', 'cuisine');
            break;
        case 'sights':
            html = renderList(currentData.top_attractions, '🏛️ Local Landmarks & Sights', 'source');
            break;
    }
    resultDiv.innerHTML = html;
}

/* ── Tab click ───────────────────────────────────────────────── */
if (panelTabs) {
    panelTabs.addEventListener('click', (e) => {
        const btn = e.target.closest('.tab-btn');
        if (!btn || !currentData) return;
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        renderTab(btn.dataset.tab);
    });
}

/* ── Auto-fetch on page load ─────────────────────────────────── */
function hideLoaders() {
    if (mapLoader) mapLoader.classList.remove('active');
    if (aiLoader)  aiLoader.classList.remove('active');
}

fetch('api/geoapify_search.php', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ city: SEARCH_CITY })
})
.then(res => res.json())
.then(data => {
    hideLoaders();

    if (data.error) {
        resultDiv.innerHTML = `
            <div style="background:rgba(255,80,80,0.1);padding:24px;border-radius:16px;
                        border:1px solid rgba(255,80,80,0.3);margin:20px 0;">
                <h3 style="color:#ff6b6b;margin:0 0 10px;">Search Error</h3>
                <p style="color:var(--text-muted);margin:0;">${data.error}</p>
            </div>`;
        return;
    }

    currentData = data;

    // Update page title & city name display
    document.title = `${data.city_name} | SmartGuide AI`;
    const nameEl = document.getElementById('cityDisplayName');
    if (nameEl) nameEl.textContent = data.city_name;

    // Render default tab
    renderTab('insights');

    // Init map + markers
    if (data.lat && data.lng) {
        initMap(data.lat, data.lng);

        const restaurantIcon = makeIcon('#ff6b6b');
        const hotelIcon      = makeIcon('#ffd93d');
        const attractIcon    = makeIcon('#6bcb77');

        (data.restaurants || []).forEach(r => {
            if (r.lat && r.lng)
                L.marker([r.lat, r.lng], { icon: restaurantIcon })
                 .bindPopup(`<strong>${r.name}</strong><br><em>${r.cuisine}</em>`)
                 .addTo(markersLayer);
        });

        (data.hotels || []).forEach(h => {
            if (h.lat && h.lng)
                L.marker([h.lat, h.lng], { icon: hotelIcon })
                 .bindPopup(`<strong>${h.name}</strong><br>${h.price_range}`)
                 .addTo(markersLayer);
        });

        (data.top_attractions || []).forEach(a => {
            if (a.lat && a.lng)
                L.marker([a.lat, a.lng], { icon: attractIcon })
                 .bindPopup(`<strong>${a.name}</strong><br><em>${a.source}</em>`)
                 .addTo(markersLayer);
        });

        if (mapLegend) mapLegend.style.display = 'flex';
    }
})
.catch(err => {
    hideLoaders();
    console.error('Fetch error:', err);
    resultDiv.innerHTML = `
        <div style="background:rgba(255,80,80,0.1);padding:24px;border-radius:16px;
                    border:1px solid rgba(255,80,80,0.3);margin:20px 0;">
            <h3 style="color:#ff6b6b;margin:0 0 10px;">Connection Error</h3>
            <p style="color:var(--text-muted);margin:0;">Could not reach the server. Make sure localhost is running.</p>
        </div>`;
});
