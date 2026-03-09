const aiForm = document.getElementById("aiForm");
const resultDiv = document.getElementById("result");
const cityInput = document.getElementById("city");
const generateBtn = document.getElementById("generateBtn");
const panelTabs = document.getElementById("panelTabs");

let map;
let markersLayer;
let currentCityData = null;

function initMap(lat, lng) {
    const pos = [parseFloat(lat), parseFloat(lng)];

    if (!map) {
        map = L.map('map', { zoomControl: true }).setView(pos, 13);

        // Geoapify tile layer with dark OSM style
        L.tileLayer(
            `https://maps.geoapify.com/v1/tile/dark-matter-yellow-roads/{z}/{x}/{y}.png?apiKey=${GEOAPIFY_API_KEY}`,
            {
                maxZoom: 20,
                attribution:
                    '&copy; <a href="https://www.geoapify.com/">Geoapify</a> &copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> contributors'
            }
        ).addTo(map);

        markersLayer = L.layerGroup().addTo(map);
    } else {
        map.setView(pos, 13);
        markersLayer.clearLayers();
    }

    // Main city marker (cyan pulse)
    const cityIcon = L.divIcon({
        className: '',
        html: `<div style="
            width:16px;height:16px;
            background:var(--neon-cyan,#00f7ff);
            border:3px solid #fff;
            border-radius:50%;
            box-shadow:0 0 12px #00f7ff, 0 0 24px #00f7ff;
        "></div>`,
        iconSize: [16, 16],
        iconAnchor: [8, 8]
    });
    L.marker(pos, { icon: cityIcon }).addTo(markersLayer);
}

// --- Tab System ---
if (panelTabs) {
    panelTabs.addEventListener('click', (e) => {
        const btn = e.target.closest('.tab-btn');
        if (!btn || !currentCityData) return;

        // Toggle Active Class
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Render Tab Content
        const tab = btn.dataset.tab;
        renderTab(tab);
    });
}

const parseMarkdown = (text) => {
    if (!text) return "";
    if (typeof marked.parse === 'function') return marked.parse(text);
    if (typeof marked === 'function') return marked(text);
    return text;
};

function renderTab(tab) {
    if (!currentCityData) return;
    
    let html = "";
    switch(tab) {
        case 'insights':
            html = `<div class="fade-in">
                        ${parseMarkdown(currentCityData.guide_markdown || "No insights available for this location.")}
                    </div>`;
            break;
            
        case 'itinerary':
            html = `<div class="fade-in">${renderItinerary(currentCityData.itinerary)}</div>`;
            break;
            
        case 'stay':
            html = `<div class="fade-in">${renderList(currentCityData.hotels, "🏨 Recommended Stays", "price_range")}</div>`;
            break;
            
        case 'dining':
            html = `<div class="fade-in">${renderList(currentCityData.restaurants, "🍽️ Exceptional Dining", "cuisine")}</div>`;
            break;
            
        case 'sights':
            // Merge AI Top Attractions and Amadeus POIs
            const sights = [];
            if (currentCityData.top_attractions) {
                currentCityData.top_attractions.forEach(a => sights.push({ name: a.name, desc: a.desc, source: "AI Expert" }));
            }
            if (currentCityData.pois) {
                currentCityData.pois.forEach(p => sights.push({ name: p.name, desc: p.category.replace(/_/g, ' '), source: "Amadeus" }));
            }
            html = `<div class="fade-in">${renderList(sights, "🏛️ Local Landmarks & Sights", "source")}</div>`;
            break;
    }
    resultDiv.innerHTML = html;
}

function renderList(items, title, metaKey) {
    if (!items || !items.length) return `<div style="text-align:center; padding: 40px; color: var(--text-muted);">No data available for this category yet.</div>`;
    
    let html = `
        <div style="color: var(--neon-cyan); font-size: 0.9rem; text-transform: uppercase; font-weight: 800; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
            <span style="width: 24px; height: 2px; background: var(--neon-cyan);"></span>
            ${title}
        </div>
        <div style="display: grid; grid-template-columns: 1fr; gap: 18px;">
    `;
    
    items.forEach(item => {
        html += `
            <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 20px; padding: 22px; transition: transform 0.3s ease; position: relative; overflow: hidden;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                    <div style="font-weight: 800; color: #fff; font-size: 1.1rem;">${item.name}</div>
                    <div style="background: rgba(0,247,255,0.1); color: var(--neon-cyan); padding: 4px 10px; border-radius: 8px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                        ${item[metaKey] || metaKey}
                    </div>
                </div>
                <div style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">${item.desc || "Information currently being synthesized..."}</div>
            </div>
        `;
    });
    
    html += `</div>`;
    return html;
}

function renderItinerary(itinerary) {
    if (!itinerary || !itinerary.length) return `<div style="text-align:center; padding: 40px; color: var(--text-muted);">Itinerary generation limit reached.</div>`;
    let html = `
        <div style="color: var(--neon-cyan); font-size: 0.9rem; text-transform: uppercase; font-weight: 800; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
            <span style="width: 24px; height: 2px; background: var(--neon-cyan);"></span>
            Strategic 3-Day Plan
        </div>
        <div style="display: flex; flex-direction: column; gap: 20px;">
    `;
    
    itinerary.forEach(day => {
        html += `
            <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 24px; padding: 25px;">
                <div style="font-weight: 800; color: #fff; margin-bottom: 20px; font-size: 1.2rem; display: flex; align-items: center; gap: 12px;">
                    <span style="background: var(--neon-cyan); color: #000; width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 900;">${day.day}</span>
                    Day ${day.day} Operations
                </div>
                <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
        `;
        day.activities.forEach(act => {
            html += `
                <div style="display: flex; gap: 20px; align-items: flex-start;">
                    <div style="color: var(--neon-cyan); font-weight: 800; font-size: 0.75rem; width: 80px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.7; padding-top: 5px;">${act.time}</div>
                    <div style="color: var(--text-muted); font-size: 0.95rem; flex: 1; border-left: 2px solid rgba(0,247,255,0.15); padding-left: 20px; line-height: 1.6;">${act.desc}</div>
                </div>
            `;
        });
        html += `</div></div>`;
    });
    
    html += `</div>`;
    return html;
}

aiForm.addEventListener("submit", function(e) {
    e.preventDefault();
    const city = cityInput.value;

    generateBtn.disabled = true;

    fetch("api/geoapify_search.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ city: city })
    })
    .then(res => res.json())
    .then(data => {
        generateBtn.disabled = false;
        if (window.hideLoaders) window.hideLoaders();

        if (data.error) {
            // Enhanced error reporting
            if (data.error.includes("quota") || data.error.includes("exceeded")) {
                resultDiv.innerHTML = `
                    <div style="background: rgba(255, 80, 80, 0.1); padding: 20px; border-radius: 16px; border: 1px solid rgba(255, 80, 80, 0.3); margin-bottom: 20px;">
                        <h3 style="color: #ff6b6b; margin-top: 0;">AI Quota Exceeded</h3>
                        <p style="color: var(--text-muted); margin-bottom: 0;">Our AI search limit has been reached for today. We are pivoting to <strong>Amadeus Geospatial Data</strong> to find points of interest for you.</p>
                    </div>
                `;
                // Even with quota error, we might have lat/lng from fallback
                if (data.lat && data.lng) {
                    currentCityData = data;
                    renderTab('sights'); // Default to sights if no guide
                }
            } else {
                alert(data.error);
                return;
            }
        }
        
        currentCityData = data;
        
        // Reset Tabs to Insights
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelector('.tab-btn[data-tab="insights"]').classList.add('active');
        
        // Render Initial Tab
        renderTab('insights');
        
        // Update Map with city centre + place markers
        if (data.lat && data.lng) {
            initMap(data.lat, data.lng);

            // Place icon factory
            function makeIcon(color) {
                return L.divIcon({
                    className: '',
                    html: `<div style="
                        width:10px;height:10px;
                        background:${color};
                        border:2px solid rgba(255,255,255,0.6);
                        border-radius:50%;
                        box-shadow:0 0 6px ${color};
                    "></div>`,
                    iconSize: [10, 10],
                    iconAnchor: [5, 5]
                });
            }

            const restaurantIcon = makeIcon('#ff6b6b');
            const hotelIcon      = makeIcon('#ffd93d');
            const attractIcon    = makeIcon('#6bcb77');

            // Add restaurant markers
            if (data.restaurants) {
                data.restaurants.forEach(r => {
                    if (r.lat && r.lng) {
                        L.marker([r.lat, r.lng], { icon: restaurantIcon })
                            .bindPopup(`<strong>${r.name}</strong><br><em>${r.cuisine}</em>`)
                            .addTo(markersLayer);
                    }
                });
            }

            // Add hotel markers
            if (data.hotels) {
                data.hotels.forEach(h => {
                    if (h.lat && h.lng) {
                        L.marker([h.lat, h.lng], { icon: hotelIcon })
                            .bindPopup(`<strong>${h.name}</strong><br>${h.price_range}`)
                            .addTo(markersLayer);
                    }
                });
            }

            // Add attraction markers
            if (data.top_attractions) {
                data.top_attractions.forEach(a => {
                    if (a.lat && a.lng) {
                        L.marker([a.lat, a.lng], { icon: attractIcon })
                            .bindPopup(`<strong>${a.name}</strong><br><em>${a.source}</em>`)
                            .addTo(markersLayer);
                    }
                });
            }
        }
    })
    .catch(err => {
        generateBtn.disabled = false;
        if (window.hideLoaders) window.hideLoaders();
        console.error("AI Error:", err);
        alert("Fail to connect to AI server.");
    });
});
