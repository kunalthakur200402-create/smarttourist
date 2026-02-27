document.addEventListener('DOMContentLoaded', () => {
    const cityList = document.getElementById('city-list');
    const placesSection = document.getElementById('places-section');
    const citiesSection = document.getElementById('cities-section');
    const placesList = document.getElementById('places-list');
    const backBtn = document.getElementById('back-btn');
    const currentCityName = document.getElementById('current-city-name');

    // Fetch Cities on Load
    fetch('api/fetch_city.php')
        .then(response => response.json())
        .then(cities => {
            cities.forEach(city => {
                const card = document.createElement('div');
                card.className = 'card';
                card.innerHTML = `
                    <img src="${city.image_url}" alt="${city.city_name}">
                    <div class="card-content">
                        <h3>${city.city_name}</h3>
                        <p>${city.description}</p>
                    </div>
                `;
                card.addEventListener('click', () => loadPlaces(city.id, city.city_name));
                cityList.appendChild(card);
            });
        })
        .catch(err => console.error('Error fetching cities:', err));

    // Load Places for a City
    function loadPlaces(cityId, cityName) {
        citiesSection.style.display = 'none';
        placesSection.style.display = 'block';
        currentCityName.textContent = cityName;
        placesList.innerHTML = ''; // Clear previous

        fetch(`api/fetch_places.php?city_id=${cityId}`)
            .then(response => response.json())
            .then(places => {
                if (places.length === 0) {
                    placesList.innerHTML = '<p>No places found for this city.</p>';
                    return;
                }
                places.forEach(place => {
                    const card = document.createElement('div');
                    card.className = 'card';
                    card.innerHTML = `
                        <img src="${place.image_url}" alt="${place.place_name}">
                        <div class="card-content">
                            <h3>${place.place_name}</h3>
                            <p>${place.description}</p>
                            <small>AI Insight: ${place.ai_description || 'No AI insight available.'}</small>
                        </div>
                    `;
                    placesList.appendChild(card);
                });
            })
            .catch(err => console.error('Error fetching places:', err));
    }

    // Back Button
    backBtn.addEventListener('click', () => {
        placesSection.style.display = 'none';
        citiesSection.style.display = 'block';
    });
});
