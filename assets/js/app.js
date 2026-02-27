let map;
let markers = [];

// Initialize Map
function initMap() {
    map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: 20.5937, lng: 78.9629 }, // India Center
        zoom: 4,
        styles: [
            {
                "featureType": "poi",
                "stylers": [{ "visibility": "off" }]
            }
        ]
    });
    
    // Initial fetch of cities
    fetchCities();
}

// Clear Markers
function clearMarkers() {
    markers.forEach(m => m.setMap(null));
    markers = [];
}

// Add Marker
function addMarker(lat, lng, title) {
    const marker = new google.maps.Marker({
        position: { lat: parseFloat(lat), lng: parseFloat(lng) },
        map: map,
        title: title,
        animation: google.maps.Animation.DROP
    });
    markers.push(marker);
    return marker;
}

// Fetch Cities
function fetchCities() {
    fetch('api/fetch_city.php')
        .then(res => res.json())
        .then(cities => {
            const list = document.getElementById('city-list');
            list.innerHTML = '';
            clearMarkers();

            cities.forEach(city => {
                // Determine lat/lng if missing (fallback)
                const lat = city.latitude || 20.0;
                const lng = city.longitude || 78.0;

                // Add to list
                const card = document.createElement('div');
                card.className = 'card';
                card.innerHTML = `
                    <img src="${city.image_url}" alt="${city.city_name}">
                    <div class="card-content">
                        <h3>${city.city_name}</h3>
                        <p>${city.description}</p>
                    </div>
                `;
                card.onclick = () => loadCityDetails(city);
                list.appendChild(card);

                // Add to map
                const marker = addMarker(lat, lng, city.city_name);
                marker.addListener("click", () => loadCityDetails(city));
            });
        });
}

// Load City Details (Places + Weather)
function loadCityDetails(city) {
    document.getElementById('cities-section').style.display = 'none';
    document.getElementById('places-section').style.display = 'block';
    document.getElementById('current-city-name').innerText = city.city_name;

    // Pan Map
    if (city.latitude && city.longitude) {
        map.panTo({ lat: parseFloat(city.latitude), lng: parseFloat(city.longitude) });
        map.setZoom(12);
    }

    // Fetch Weather
    fetchWeather(city.city_name);

    // Fetch Places
    fetch(`api/fetch_places.php?city_id=${city.id}`)
        .then(res => res.json())
        .then(places => {
            const list = document.getElementById('places-list');
            list.innerHTML = '';
            clearMarkers(); // Clear city markers, show place markers

            places.forEach(place => {
                 const card = document.createElement('div');
                card.className = 'card';
                card.innerHTML = `
                    <div class="card-content">
                        <h3>${place.place_name}</h3>
                        <p>${place.description}</p>
                    </div>
                `;
                list.appendChild(card);

                if (place.latitude && place.longitude) {
                    addMarker(place.latitude, place.longitude, place.place_name);
                }
            });
        });
        
    // Save to History
    saveHistory(city.city_name);
}

// Fetch Weather
function fetchWeather(cityName) {
    const widget = document.getElementById('weather-widget');
    widget.innerHTML = 'Loading Weather...';
    
    fetch(`api/get_weather.php?city=${cityName}`)
        .then(res => res.json())
        .then(data => {
            if (data.main) {
                widget.innerHTML = `
                    <h3>${Math.round(data.main.temp)}°C</h3>
                    <p>${data.weather[0].description}</p>
                    <small>Humidity: ${data.main.humidity}%</small>
                `;
            } else {
                widget.innerHTML = 'Weather Unavailable (Check API Key)';
            }
        })
        .catch(() => widget.innerHTML = 'Weather Error');
}

// Save History
function saveHistory(query) {
    fetch('api/save_history.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ query: query })
    });
}

// Back Button
document.getElementById('back-btn').addEventListener('click', () => {
    document.getElementById('places-section').style.display = 'none';
    document.getElementById('cities-section').style.display = 'block';
    map.setZoom(4);
    fetchCities(); // Reset map markers to cities
});

// Search functionality
document.getElementById('city-search').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        const query = this.value;
        // Simple client-side filter for demo, or could be backend search
        // For now, we just save history and alert as a mock search
        saveHistory(query);
        alert('Search saved: ' + query);
    }
});
