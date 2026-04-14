@php
    $globeRestaurantSource = collect($restaurants ?? $globeRestaurants ?? [])->values();
    $globeRestaurantsPayload = $globeRestaurantSource->map(fn ($restaurant) => [
        'id' => (int) $restaurant->id,
        'name' => $restaurant->name,
        'url' => route('restaurant.show', $restaurant),
        'logo' => $restaurant->logo ? Storage::url($restaurant->logo) : null,
        'latitude' => $restaurant->latitude !== null ? (float) $restaurant->latitude : null,
        'longitude' => $restaurant->longitude !== null ? (float) $restaurant->longitude : null,
        'address' => trim((string) head(explode(',', (string) $restaurant->address))),
        'isOpen' => (bool) $restaurant->isOpenNow(),
    ])->all();
@endphp

<section class="mt-16 sm:mt-20 lg:mt-24">
    <div class="mx-auto grid max-w-6xl gap-8 px-4 md:gap-10 lg:grid-cols-2 lg:items-center lg:gap-12">
        <div class="flex h-full items-center">
            <h2 class="w-full text-center text-3xl font-black leading-tight text-gray-900 outfit sm:text-5xl lg:text-left lg:text-6xl">
                Land on your next favorite restaurant.
            </h2>
        </div>

        <div class="relative overflow-hidden rounded-[2rem] p-1.5 sm:rounded-[2.4rem] sm:p-2.5 lg:rounded-[2.75rem] lg:p-3">
            <div
                id="home-restaurant-globe"
                class="relative h-[32rem] w-full overflow-hidden rounded-[1.7rem] sm:h-[38rem] sm:rounded-[2rem] md:h-[44rem] lg:h-[48rem] lg:rounded-[2.25rem]"
                data-restaurants='@json($globeRestaurantsPayload)'
            >
                <div class="flex h-full items-center justify-center text-sm font-bold text-slate-300/70">
                    Loading restaurant globe...
                </div>
                <noscript>
                    <div class="flex h-full items-center justify-center px-6 text-center text-sm font-bold text-slate-200">
                        Enable JavaScript to explore the interactive restaurant globe.
                    </div>
                </noscript>
            </div>
            <div
                id="home-restaurant-globe-tooltip"
                class="pointer-events-none absolute left-0 top-0 z-20 hidden min-w-[180px] rounded-2xl bg-white px-4 py-3 text-gray-900 shadow-2xl shadow-slate-900/20"
            ></div>
        </div>
    </div>
</section>

@once
    @push('scripts')
        <script type="importmap">
            {
                "imports": {
                    "three": "https://cdn.jsdelivr.net/npm/three@0.179.1/build/three.module.js"
                }
            }
        </script>
        <script type="module">
            import * as THREE from 'three';
            import { OrbitControls } from 'https://cdn.jsdelivr.net/npm/three@0.179.1/examples/jsm/controls/OrbitControls.js';

            const globeElement = document.getElementById('home-restaurant-globe');
            const tooltipElement = document.getElementById('home-restaurant-globe-tooltip');

            if (globeElement) {
                const restaurants = JSON.parse(globeElement.dataset.restaurants || '[]');

                const scene = new THREE.Scene();
                const camera = new THREE.PerspectiveCamera(42, 1, 0.1, 1000);
                camera.position.set(0, 1.1, 19);

                const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
                renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
                renderer.outputColorSpace = THREE.SRGBColorSpace;
                renderer.setClearColor(0x000000, 0);
                renderer.domElement.className = 'h-full w-full';
                renderer.domElement.style.cursor = 'grab';

                globeElement.innerHTML = '';
                globeElement.appendChild(renderer.domElement);

                const controls = new OrbitControls(camera, renderer.domElement);
                controls.enablePan = false;
                controls.enableDamping = true;
                controls.dampingFactor = 0.065;
                controls.rotateSpeed = 0.7;
                controls.minDistance = 14;
                controls.maxDistance = 28;
                controls.autoRotate = restaurants.length > 0;
                controls.autoRotateSpeed = 0.45;

                const rootGroup = new THREE.Group();
                scene.add(rootGroup);

                const earthRadius = 5.2;
                const clock = new THREE.Clock();
                const textureLoader = new THREE.TextureLoader();
                textureLoader.setCrossOrigin('anonymous');
                
                // Use local assets for performance and CORS stability
                const assetBase = '/assets/globe/';
                const textures = {
                    day: assetBase + 'earth-blue-marble.jpg',
                    bump: assetBase + 'earth-topology.png',
                    clouds: assetBase + 'earth-clouds.png',
                    night: assetBase + 'earth-night.jpg'
                };

                // Advanced Grouping
                const globeGroup = new THREE.Group();
                rootGroup.add(globeGroup);

                const earthGroup = new THREE.Group();
                globeGroup.add(earthGroup);

                // Initialize Earth with realistic textures
                const earthMaterial = new THREE.MeshStandardMaterial({
                    map: textureLoader.load(textures.day),
                    bumpMap: textureLoader.load(textures.bump),
                    bumpScale: 0.15,
                    roughness: 0.8,
                    metalness: 0.1,
                    emissiveMap: textureLoader.load(textures.night),
                    emissive: new THREE.Color(0xffff88),
                    emissiveIntensity: 0.35,
                });

                const earth = new THREE.Mesh(
                    new THREE.SphereGeometry(earthRadius, 128, 128),
                    earthMaterial
                );
                earthGroup.add(earth);

                // Animated Clouds Layer
                const cloudMaterial = new THREE.MeshStandardMaterial({
                    map: textureLoader.load(textures.clouds),
                    transparent: true,
                    opacity: 0.4,
                    depthWrite: false,
                    blending: THREE.AdditiveBlending
                });

                const clouds = new THREE.Mesh(
                    new THREE.SphereGeometry(earthRadius + 0.14, 96, 96),
                    cloudMaterial
                );
                earthGroup.add(clouds);

                // Realistic Atmosphere
                const atmosphere = new THREE.Mesh(
                    new THREE.SphereGeometry(earthRadius * 1.015, 64, 64),
                    new THREE.MeshBasicMaterial({
                        color: 0x4cc9f0,
                        transparent: true,
                        opacity: 0.15,
                        side: THREE.BackSide,
                        blending: THREE.AdditiveBlending,
                    })
                );
                globeGroup.add(atmosphere);

                const outerGlow = new THREE.Mesh(
                    new THREE.SphereGeometry(earthRadius * 1.15, 64, 64),
                    new THREE.MeshBasicMaterial({
                        color: 0x22d3ee,
                        transparent: true,
                        opacity: 0.08,
                        side: THREE.BackSide,
                        blending: THREE.AdditiveBlending,
                    })
                );
                globeGroup.add(outerGlow);

                // Lighting
                const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
                const directionalLight = new THREE.DirectionalLight(0xffffff, 2.5);
                directionalLight.position.set(20, 15, 30);
                scene.add(ambientLight, directionalLight);

                const markerSprites = [];
                const animatedMarkers = [];
                const surfaceAccents = [];
                const raycaster = new THREE.Raycaster();
                raycaster.params.Sprite.threshold = 0.65;
                const pointer = new THREE.Vector2(2, 2);
                let hoveredMarker = null;
                let pointerDownPosition = null;
                let hasDragged = false;
                let isControlDragging = false;

                // Starfield Background
                const createStars = () => {
                    const geometry = new THREE.BufferGeometry();
                    const count = 3000;
                    const positions = new Float32Array(count * 3);
                    for (let i = 0; i < count; i++) {
                        const r = 50 + Math.random() * 80;
                        const theta = Math.random() * Math.PI * 2;
                        const phi = Math.acos(2 * Math.random() - 1);
                        positions[i * 3] = r * Math.sin(phi) * Math.cos(theta);
                        positions[i * 3 + 1] = r * Math.cos(phi);
                        positions[i * 3 + 2] = r * Math.sin(phi) * Math.sin(theta);
                    }
                    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
                    return new THREE.Points(geometry, new THREE.PointsMaterial({
                        color: 0xffffff,
                        size: 0.1,
                        transparent: true,
                        opacity: 0.8,
                        sizeAttenuation: true
                    }));
                };
                const starField = createStars();
                scene.add(starField);

                // Populate Restaurants
                Promise.all(restaurants.map((restaurant, index) => createMarker(restaurant, index, restaurants.length)));

                // Interaction Logic
                controls.addEventListener('start', () => {
                    isControlDragging = true;
                    controls.autoRotate = false;
                    renderer.domElement.style.cursor = 'grabbing';
                });

                controls.addEventListener('end', () => {
                    isControlDragging = false;
                    updateCursor();
                });

                renderer.domElement.addEventListener('pointerdown', (event) => {
                    pointerDownPosition = { x: event.clientX, y: event.clientY };
                    hasDragged = false;
                });

                renderer.domElement.addEventListener('pointermove', (event) => {
                    if (pointerDownPosition) {
                        const d = Math.hypot(event.clientX - pointerDownPosition.x, event.clientY - pointerDownPosition.y);
                        hasDragged = hasDragged || d > 6;
                    }
                    updatePointer(event);
                    updateHoverState();
                });

                renderer.domElement.addEventListener('pointerup', (event) => {
                    updatePointer(event);
                    updateHoverState();
                    if (!hasDragged) {
                        const intersected = getIntersectedMarker();
                        if (intersected?.object?.userData?.restaurant?.url) {
                            window.location.href = intersected.object.userData.restaurant.url;
                        }
                    }
                    pointerDownPosition = null;
                    hasDragged = false;
                    updateCursor();
                });

                renderer.domElement.addEventListener('pointerleave', () => {
                    pointer.set(2, 2);
                    hoveredMarker = null;
                    hideTooltip();
                    updateCursor();
                });

                const resizeObserver = new ResizeObserver(() => resizeRenderer());
                resizeObserver.observe(globeElement);
                window.addEventListener('resize', resizeRenderer);
                resizeRenderer();

                animate();

                // Core Functions
                function resizeRenderer() {
                    const { clientWidth, clientHeight } = globeElement;
                    if (!clientWidth || !clientHeight) return;
                    renderer.setSize(clientWidth, clientHeight, false);
                    camera.aspect = clientWidth / clientHeight;
                    camera.position.set(0, clientWidth < 640 ? 1.2 : 1.1, clientWidth < 640 ? 26 : clientWidth < 1024 ? 20 : 19);
                    camera.updateProjectionMatrix();
                    controls.update();
                }

                function updatePointer(e) {
                    const rect = renderer.domElement.getBoundingClientRect();
                    pointer.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
                    pointer.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;
                }

                function updateHoverState() {
                    const intersected = getIntersectedMarker();
                    if (!intersected) {
                        hoveredMarker = null;
                        hideTooltip();
                        updateCursor();
                        return;
                    }
                    hoveredMarker = intersected.object;
                    showTooltip(hoveredMarker.userData.restaurant, hoveredMarker);
                    updateCursor();
                }

                function getIntersectedMarker() {
                    raycaster.setFromCamera(pointer, camera);
                    const intersections = raycaster.intersectObjects(markerSprites, false);
                    return intersections.find(i => isMarkerFacingCamera(i.object)) || null;
                }

                function updateCursor() {
                    if (isControlDragging) {
                        renderer.domElement.style.cursor = 'grabbing';
                        return;
                    }
                    renderer.domElement.style.cursor = hoveredMarker ? 'pointer' : 'grab';
                }

                function showTooltip(restaurant, marker) {
                    if (!tooltipElement) return;
                    const pos = new THREE.Vector3();
                    marker.getWorldPosition(pos);
                    const visibility = pos.clone().normalize().dot(camera.position.clone().normalize());
                    if (visibility < 0.12) {
                        hideTooltip();
                        return;
                    }
                    const projected = pos.project(camera);
                    const x = globeElement.offsetLeft + (projected.x * 0.5 + 0.5) * globeElement.clientWidth;
                    const y = globeElement.offsetTop + (-projected.y * 0.5 + 0.5) * globeElement.clientHeight;
                    tooltipElement.innerHTML = `
                        <div class="text-[10px] font-black uppercase tracking-[0.24em] ${restaurant.isOpen ? 'text-emerald-500' : 'text-rose-500'}">${restaurant.isOpen ? 'Open Now' : 'Closed'}</div>
                        <div class="mt-1 text-sm font-black text-gray-900">${restaurant.name}</div>
                        <div class="mt-1 text-xs font-medium text-gray-500">${restaurant.address || 'Restaurant view'}</div>
                    `;
                    tooltipElement.classList.remove('hidden');
                    tooltipElement.style.transform = `translate(${x}px, ${y}px) translate(-50%, calc(-100% - 18px))`;
                }

                function hideTooltip() { tooltipElement?.classList.add('hidden'); }

                function isMarkerFacingCamera(marker) {
                    const pos = new THREE.Vector3();
                    marker.getWorldPosition(pos);
                    return pos.clone().normalize().dot(camera.position.clone().normalize()) > 0.12;
                }

                async function createMarker(restaurant, index, total) {
                    const { latitude, longitude, usedFallback } = resolveCoordinates(restaurant, index, total);
                    const markerTexture = await createMarkerTexture(restaurant);
                    markerTexture.colorSpace = THREE.SRGBColorSpace;

                    const sprite = new THREE.Sprite(new THREE.SpriteMaterial({ map: markerTexture, depthWrite: false }));
                    const surfPos = latLngToVector(latitude, longitude, earthRadius + 0.05);
                    const badgePos = latLngToVector(latitude, longitude, earthRadius + 0.9);

                    sprite.position.copy(badgePos);
                    sprite.scale.setScalar(usedFallback ? 1.25 : 1.4);
                    sprite.userData = { restaurant, baseScale: usedFallback ? 1.25 : 1.4 };

                    const stem = new THREE.Line(
                        new THREE.BufferGeometry().setFromPoints([surfPos, badgePos.clone().multiplyScalar(0.98)]),
                        new THREE.LineBasicMaterial({ color: 0x7dd3fc, transparent: true, opacity: 0.6 })
                    );

                    const accent = new THREE.Mesh(
                        new THREE.SphereGeometry(0.08, 12, 12),
                        new THREE.MeshBasicMaterial({ color: restaurant.isOpen ? 0x34d399 : 0xf43f5e })
                    );
                    accent.position.copy(surfPos);

                    globeGroup.add(stem, accent, sprite);
                    markerSprites.push(sprite);
                    animatedMarkers.push(sprite);
                    surfaceAccents.push(accent);
                }

                function resolveCoordinates(restaurant, index, total) {
                    if (Number.isFinite(restaurant.latitude) && Number.isFinite(restaurant.longitude)) {
                        return { latitude: restaurant.latitude, longitude: restaurant.longitude, usedFallback: false };
                    }
                    const goldenAngle = Math.PI * (3 - Math.sqrt(5));
                    const y = 1 - ((index + 1) / (total + 1)) * 2;
                    const r = Math.sqrt(1 - y * y);
                    const theta = goldenAngle * (index + 1);
                    return {
                        latitude: THREE.MathUtils.radToDeg(Math.asin(y)),
                        longitude: THREE.MathUtils.radToDeg(Math.atan2(Math.sin(theta) * r, Math.cos(theta) * r)),
                        usedFallback: true
                    };
                }

                function latLngToVector(lat, lng, r) {
                    const phi = THREE.MathUtils.degToRad(90 - lat);
                    const theta = THREE.MathUtils.degToRad(lng + 180);
                    return new THREE.Vector3(-r * Math.sin(phi) * Math.cos(theta), r * Math.cos(phi), r * Math.sin(phi) * Math.sin(theta));
                }

                async function createMarkerTexture(restaurant) {
                    const canvas = document.createElement('canvas');
                    canvas.width = 256; canvas.height = 256;
                    const ctx = canvas.getContext('2d');
                    ctx.fillStyle = 'rgba(15, 23, 42, 0.9)';
                    ctx.beginPath(); ctx.arc(128, 128, 110, 0, Math.PI * 2); ctx.fill();
                    ctx.strokeStyle = restaurant.isOpen ? '#10b981' : '#f43f5e';
                    ctx.lineWidth = 12; ctx.stroke();
                    ctx.save(); ctx.beginPath(); ctx.arc(128, 110, 75, 0, Math.PI * 2); ctx.clip();
                    if (restaurant.logo) {
                        try {
                            const img = await new Promise((res, rej) => {
                                const i = new Image(); i.crossOrigin = 'anonymous';
                                i.onload = () => res(i); i.onerror = rej; i.src = restaurant.logo;
                            });
                            ctx.drawImage(img, 53, 35, 150, 150);
                        } catch(e) { /* fallback below */ }
                    }
                    ctx.fillStyle = 'white'; ctx.font = '900 80px Outfit, sans-serif'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                    if (!restaurant.logo) ctx.fillText(restaurant.name.charAt(0).toUpperCase(), 128, 110);
                    ctx.restore();
                    ctx.fillStyle = 'white'; ctx.font = '900 24px Outfit, sans-serif'; ctx.textAlign = 'center';
                    ctx.fillText(restaurant.name.length > 15 ? restaurant.name.slice(0, 12) + '...' : restaurant.name, 128, 220);
                    return new THREE.CanvasTexture(canvas);
                }

                function animate() {
                    requestAnimationFrame(animate);
                    const t = clock.getElapsedTime();
                    clouds.rotation.y += 0.0006;
                    starField.rotation.y -= 0.0001;
                    animatedMarkers.forEach((m, i) => {
                        const pulse = 1 + Math.sin(t * 2 + i) * 0.05;
                        const s = hoveredMarker === m ? m.userData.baseScale * 1.15 : m.userData.baseScale * pulse;
                        m.scale.set(s, s, s);
                    });
                    surfaceAccents.forEach((a, i) => {
                        a.scale.setScalar(0.8 + Math.sin(t * 3 + i) * 0.2);
                    });
                    controls.update();
                    if (hoveredMarker) showTooltip(hoveredMarker.userData.restaurant, hoveredMarker);
                    renderer.render(scene, camera);
                }
            }
        </script>
    @endpush
@endonce
