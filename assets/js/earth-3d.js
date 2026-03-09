const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
renderer.setSize(window.innerWidth, window.innerHeight);
renderer.setPixelRatio(window.devicePixelRatio);
document.getElementById('canvas-container').appendChild(renderer.domElement);

// --- Lighting ---
const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
scene.add(ambientLight);

const sunLight = new THREE.DirectionalLight(0xffffff, 1.2);
sunLight.position.set(5, 3, 5);
scene.add(sunLight);

// --- Earth Setup (Realistic) ---
const earthGroup = new THREE.Group();
scene.add(earthGroup);

const loader = new THREE.TextureLoader();
// Using high-res textures from three.js examples repository
const earthTexture = loader.load('https://raw.githubusercontent.com/mrdoob/three.js/master/examples/textures/planets/earth_atmos_2048.jpg');
const earthNormal = loader.load('https://raw.githubusercontent.com/mrdoob/three.js/master/examples/textures/planets/earth_normal_2048.jpg');
const earthSpec = loader.load('https://raw.githubusercontent.com/mrdoob/three.js/master/examples/textures/planets/earth_specular_2048.jpg');
const cloudTexture = loader.load('https://raw.githubusercontent.com/mrdoob/three.js/master/examples/textures/planets/earth_clouds_1024.png');

const globeGeometry = new THREE.SphereGeometry(5, 64, 64);
const globeMaterial = new THREE.MeshStandardMaterial({
    map: earthTexture,
    normalMap: earthNormal,
    roughnessMap: earthSpec,
    metalness: 0.1,
    roughness: 0.8
});
const earthMesh = new THREE.Mesh(globeGeometry, globeMaterial);
earthGroup.add(earthMesh);

// Clouds Layer
const cloudGeometry = new THREE.SphereGeometry(5.05, 64, 64);
const cloudMaterial = new THREE.MeshPhongMaterial({
    map: cloudTexture,
    transparent: true,
    opacity: 0.4
});
const clouds = new THREE.Mesh(cloudGeometry, cloudMaterial);
earthGroup.add(clouds);

// Atmospheric Glow
const glowGeometry = new THREE.SphereGeometry(5.15, 64, 64);
const glowMaterial = new THREE.MeshBasicMaterial({
    color: 0x00f7ff,
    transparent: true,
    opacity: 0.12,
    side: THREE.BackSide
});
const glowSphere = new THREE.Mesh(glowGeometry, glowMaterial);
earthGroup.add(glowSphere);

// Rim Lighting (Backlight for halo)
const rimLight = new THREE.PointLight(0x00f7ff, 2, 20);
rimLight.position.set(-5, 0, -10);
scene.add(rimLight);

// --- Neural Particles (Swarm) ---
const particlesGeometry = new THREE.BufferGeometry();
const particlesCount = 2000;
const posArray = new Float32Array(particlesCount * 3);

for(let i=0; i < particlesCount * 3; i++) {
    posArray[i] = (Math.random() - 0.5) * 20;
}

particlesGeometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));

const particleMaterial = new THREE.PointsMaterial({
    size: 0.008,
    color: 0x00f7ff,
    transparent: true,
    opacity: 0.6,
    blending: THREE.AdditiveBlending
});

const particlesMesh = new THREE.Points(particlesGeometry, particleMaterial);
scene.add(particlesMesh);

// Move Earth down from center
earthGroup.position.y = -2.5;

camera.position.z = 12;

// --- Satellites ---
const satellites = [];
const satelliteGroup = new THREE.Group();
satelliteGroup.position.y = -2.5; // Match Earth position
scene.add(satelliteGroup);

function createSatellite(color = 0xffffff, dist = 7, speed = 0.01, inclination = 0) {
    const satellite = new THREE.Group();
    
    // Satellite Body
    const bodyGeom = new THREE.BoxGeometry(0.15, 0.15, 0.15);
    const bodyMat = new THREE.MeshStandardMaterial({ color: 0x888888, metalness: 0.8, roughness: 0.2 });
    const body = new THREE.Mesh(bodyGeom, bodyMat);
    satellite.add(body);

    // Solar Panels
    const panelGeom = new THREE.PlaneGeometry(0.5, 0.2);
    const panelMat = new THREE.MeshStandardMaterial({ color: 0x00aaff, side: THREE.DoubleSide, emissive: 0x003366 });
    
    const panelLeft = new THREE.Mesh(panelGeom, panelMat);
    panelLeft.position.x = -0.35;
    satellite.add(panelLeft);

    const panelRight = new THREE.Mesh(panelGeom, panelMat);
    panelRight.position.x = 0.35;
    satellite.add(panelRight);

    // Initial position on orbit
    const orbitContainer = new THREE.Group();
    orbitContainer.rotation.z = inclination;
    orbitContainer.add(satellite);
    satellite.position.x = dist;
    
    satelliteGroup.add(orbitContainer);
    
    return {
        container: orbitContainer,
        sat: satellite,
        speed: speed,
        dist: dist,
        angle: Math.random() * Math.PI * 2
    };
}

satellites.push(createSatellite(0x00f7ff, 6.5, 0.005, 0.5));
satellites.push(createSatellite(0x00f7ff, 7.5, 0.008, -0.8));
satellites.push(createSatellite(0x00f7ff, 8.2, 0.003, 1.2));

// --- Interaction Logic ---
let isDragging = false;
let previousMousePosition = { x: 0, y: 0 };
let mouseX = 0;
let mouseY = 0;

document.addEventListener('mousedown', (e) => {
    isDragging = true;
    previousMousePosition = { x: e.clientX, y: e.clientY };
});

document.addEventListener('mousemove', (e) => {
    mouseX = (e.clientX / window.innerWidth) - 0.5;
    mouseY = (e.clientY / window.innerHeight) - 0.5;

    if (isDragging) {
        const deltaMove = {
            x: e.clientX - previousMousePosition.x,
            y: e.clientY - previousMousePosition.y
        };

        // Manual rotation based on drag
        earthGroup.rotation.y += deltaMove.x * 0.005;
        earthGroup.rotation.x += deltaMove.y * 0.005;

        previousMousePosition = { x: e.clientX, y: e.clientY };
    }
});

document.addEventListener('mouseup', () => {
    isDragging = false;
});

function animate() {
    requestAnimationFrame(animate);
    
    // Constant Auto-rotation (always active)
    earthGroup.rotation.y += 0.0025;
    if (clouds) clouds.rotation.y += 0.0012; // Rotate clouds slightly slower
    
    // Subtle Mouse Tilt (Parallax) - but don't reset Y rotation
    if (!isDragging) {
        const targetX = mouseX * 0.5;
        const targetY = -mouseY * 0.5;
        
        // Only influence X rotation (tilt) and a small additive Y offset
        earthGroup.rotation.x += (targetY - earthGroup.rotation.x) * 0.05;
    }

    particlesMesh.rotation.y += 0.0008;
    
    // Neural particles follow mouse subtly (always)
    const pTargetX = mouseX * 0.8;
    particlesMesh.rotation.y += (pTargetX - (particlesMesh.rotation.y % (Math.PI * 2))) * 0.005;

    // Satellite Orbits
    satellites.forEach(s => {
        s.angle += s.speed;
        s.sat.position.x = Math.cos(s.angle) * s.dist;
        s.sat.position.z = Math.sin(s.angle) * s.dist;
        
        // Orient satellites to face "forward" in their orbit
        s.sat.rotation.y = s.angle + Math.PI / 2;
    });

    renderer.render(scene, camera);
}

window.addEventListener('resize', () => {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
});

animate();
