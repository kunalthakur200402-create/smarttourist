const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(75, window.innerWidth/window.innerHeight, 0.1, 1000);
const renderer = new THREE.WebGLRenderer({alpha:true, antialias: true});
renderer.setSize(window.innerWidth, window.innerHeight);
renderer.setPixelRatio(window.devicePixelRatio);
document.getElementById("canvas-container").appendChild(renderer.domElement);

// Geometry & Material
const geometry = new THREE.SphereGeometry(5, 40, 40);
const material = new THREE.MeshBasicMaterial({
    wireframe: true,
    color: 0x00f7ff,
    transparent: true,
    opacity: 0.2
});
const globe = new THREE.Mesh(geometry, material);
globe.rotation.x = 0.2; // Slight tilt
scene.add(globe);

camera.position.z = 12;

function animate(){
    requestAnimationFrame(animate);
    globe.rotation.y += 0.002;
    renderer.render(scene, camera);
}

window.addEventListener('resize', () => {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
});

animate();