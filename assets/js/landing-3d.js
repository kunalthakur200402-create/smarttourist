const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
renderer.setSize(window.innerWidth, window.innerHeight);
renderer.setPixelRatio(window.devicePixelRatio);
document.getElementById('canvas-container').appendChild(renderer.domElement);

// --- Starfield Setup ---
const starsGeometry = new THREE.BufferGeometry();
const starsCount = 5000;
const posArray = new Float32Array(starsCount * 3);

for(let i=0; i < starsCount * 3; i++) {
    posArray[i] = (Math.random() - 0.5) * 100;
}
starsGeometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));

const starsMaterial = new THREE.PointsMaterial({
    size: 0.05,
    color: 0x00f7ff,
    transparent: true,
    opacity: 0.8,
    blending: THREE.AdditiveBlending
});

const starsMesh = new THREE.Points(starsGeometry, starsMaterial);
scene.add(starsMesh);

// --- Neural Web Connections ---
// Creating a smaller set of "nodes" for the web
const nodesGeometry = new THREE.BufferGeometry();
const nodesCount = 100;
const nodesPos = new Float32Array(nodesCount * 3);
for(let i=0; i < nodesCount * 3; i++) {
    nodesPos[i] = (Math.random() - 0.5) * 40;
}
nodesGeometry.setAttribute('position', new THREE.BufferAttribute(nodesPos, 3));

const lineMaterial = new THREE.LineBasicMaterial({
    color: 0x00f7ff,
    transparent: true,
    opacity: 0.1
});

const lines = new THREE.Group();
scene.add(lines);

// Connecting random nodes to create a "web"
const points = [];
for(let i=0; i<nodesCount; i++) {
    const x = nodesPos[i*3];
    const y = nodesPos[i*3+1];
    const z = nodesPos[i*3+2];
    points.push(new THREE.Vector3(x, y, z));
}

for(let i=0; i<nodesCount; i++) {
    for(let j=i+1; j<nodesCount; j++) {
        const dist = points[i].distanceTo(points[j]);
        if(dist < 8) {
            const lineGeometry = new THREE.BufferGeometry().setFromPoints([points[i], points[j]]);
            const line = new THREE.Line(lineGeometry, lineMaterial);
            lines.add(line);
        }
    }
}

camera.position.z = 30;

let mouseX = 0;
let mouseY = 0;

document.addEventListener('mousemove', (event) => {
    mouseX = (event.clientX / window.innerWidth) - 0.5;
    mouseY = (event.clientY / window.innerHeight) - 0.5;
});

function animate() {
    requestAnimationFrame(animate);
    
    // Smooth cinematic rotation
    starsMesh.rotation.y += 0.0005;
    lines.rotation.y += 0.0003;
    
    // Mouse Parallax Interaction
    const targetX = mouseX * 5;
    const targetY = -mouseY * 5;
    
    camera.position.x += (targetX - camera.position.x) * 0.05;
    camera.position.y += (targetY - camera.position.y) * 0.05;
    camera.lookAt(scene.position);

    renderer.render(scene, camera);
}

window.addEventListener('resize', () => {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
});

animate();
