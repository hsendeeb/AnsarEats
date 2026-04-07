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
                const markerSprites = [];
                const animatedMarkers = [];
                const surfaceAccents = [];
                const raycaster = new THREE.Raycaster();
                raycaster.params.Sprite.threshold = 0.65;
                const pointer = new THREE.Vector2(2, 2);
                const clock = new THREE.Clock();
                let hoveredMarker = null;
                let pointerDownPosition = null;
                let hasDragged = false;
                let isControlDragging = false;

                const ambientLight = new THREE.AmbientLight(0x9fd4ff, 1.15);
                const directionalLight = new THREE.DirectionalLight(0xffffff, 1.9);
                directionalLight.position.set(10, 8, 14);
                const rimLight = new THREE.DirectionalLight(0x4cc9f0, 1.4);
                rimLight.position.set(-12, -3, -10);
                scene.add(ambientLight, directionalLight, rimLight);

                const globeGroup = new THREE.Group();
                rootGroup.add(globeGroup);

                const earth = new THREE.Mesh(
                    new THREE.SphereGeometry(earthRadius, 96, 96),
                    new THREE.MeshStandardMaterial({
                        map: createEarthTexture(),
                        roughness: 0.96,
                        metalness: 0.02,
                        emissive: new THREE.Color('#082032'),
                        emissiveIntensity: 0.5,
                    })
                );
                globeGroup.add(earth);

                const clouds = new THREE.Mesh(
                    new THREE.SphereGeometry(earthRadius + 0.08, 64, 64),
                    new THREE.MeshStandardMaterial({
                        map: createCloudTexture(),
                        transparent: true,
                        opacity: 0.3,
                        depthWrite: false,
                    })
                );
                globeGroup.add(clouds);

                const atmosphere = new THREE.Mesh(
                    new THREE.SphereGeometry(earthRadius + 0.55, 64, 64),
                    new THREE.MeshBasicMaterial({
                        color: 0x4cc9f0,
                        transparent: true,
                        opacity: 0.14,
                        side: THREE.BackSide,
                        blending: THREE.AdditiveBlending,
                    })
                );
                globeGroup.add(atmosphere);

                const starField = new THREE.Points(createStarsGeometry(), new THREE.PointsMaterial({
                    color: 0xc6edff,
                    size: 0.18,
                    transparent: true,
                    opacity: 0.9,
                    sizeAttenuation: true,
                }));
                scene.add(starField);

                Promise.all(restaurants.map((restaurant, index) => createMarker(restaurant, index, restaurants.length)))
                    .catch(() => {});

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
                        const distance = Math.hypot(event.clientX - pointerDownPosition.x, event.clientY - pointerDownPosition.y);
                        hasDragged = hasDragged || distance > 6;
                    }

                    updatePointer(event);
                    updateHoverState();
                });

                renderer.domElement.addEventListener('pointerup', (event) => {
                    updatePointer(event);
                    updateHoverState();

                    if (!hasDragged) {
                        const clickedMarker = getIntersectedMarker();
                        if (clickedMarker?.object?.userData?.restaurant?.url) {
                            window.location.href = clickedMarker.object.userData.restaurant.url;
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

                function resizeRenderer() {
                    const { clientWidth, clientHeight } = globeElement;
                    if (!clientWidth || !clientHeight) {
                        return;
                    }

                    renderer.setSize(clientWidth, clientHeight, false);
                    camera.aspect = clientWidth / clientHeight;
                    camera.position.set(0, clientWidth < 640 ? 1.2 : 1.1, clientWidth < 640 ? 21 : clientWidth < 1024 ? 20 : 19);
                    camera.updateProjectionMatrix();
                    controls.update();
                }

                function updatePointer(event) {
                    const rect = renderer.domElement.getBoundingClientRect();
                    pointer.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
                    pointer.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
                }

                function updateHoverState() {
                    const intersection = getIntersectedMarker();

                    if (!intersection) {
                        hoveredMarker = null;
                        hideTooltip();
                        updateCursor();
                        return;
                    }

                    hoveredMarker = intersection.object;
                    const restaurant = hoveredMarker.userData.restaurant;

                    showTooltip(restaurant, hoveredMarker);
                    updateCursor();
                }

                function getIntersectedMarker() {
                    raycaster.setFromCamera(pointer, camera);
                    const intersections = raycaster.intersectObjects(markerSprites, false);

                    return intersections.find((intersection) => isMarkerFacingCamera(intersection.object)) ?? null;
                }

                function updateCursor() {
                    if (isControlDragging) {
                        renderer.domElement.style.cursor = 'grabbing';
                        return;
                    }

                    renderer.domElement.style.cursor = hoveredMarker ? 'pointer' : 'grab';
                }

                function showTooltip(restaurant, marker) {
                    if (!tooltipElement) {
                        return;
                    }

                    const worldPosition = new THREE.Vector3();
                    marker.getWorldPosition(worldPosition);

                    const visibility = worldPosition.clone().normalize().dot(camera.position.clone().normalize());
                    if (visibility < 0.12) {
                        hideTooltip();
                        return;
                    }

                    const projected = worldPosition.project(camera);
                    const x = globeElement.offsetLeft + (projected.x * 0.5 + 0.5) * globeElement.clientWidth;
                    const y = globeElement.offsetTop + (-projected.y * 0.5 + 0.5) * globeElement.clientHeight;

                    tooltipElement.innerHTML = `
                        <div class="text-[10px] font-black uppercase tracking-[0.24em] ${restaurant.isOpen ? 'text-emerald-500' : 'text-rose-500'}">${restaurant.isOpen ? 'Open Now' : 'Closed'}</div>
                        <div class="mt-1 text-sm font-black text-gray-900">${restaurant.name}</div>
                        <div class="mt-1 text-xs font-medium text-gray-500">${restaurant.address || 'Restaurant page'}</div>
                        <div class="mt-2 text-[11px] font-semibold text-emerald-600">Click to view restaurant</div>
                    `;
                    tooltipElement.classList.remove('hidden');
                    tooltipElement.style.transform = `translate(${x}px, ${y}px) translate(-50%, calc(-100% - 18px))`;
                }

                function hideTooltip() {
                    tooltipElement?.classList.add('hidden');
                }

                function isMarkerFacingCamera(marker) {
                    const worldPosition = new THREE.Vector3();
                    marker.getWorldPosition(worldPosition);

                    return worldPosition.clone().normalize().dot(camera.position.clone().normalize()) > 0.12;
                }

                async function createMarker(restaurant, index, total) {
                    const { latitude, longitude, usedFallback } = resolveCoordinates(restaurant, index, total);
                    const markerTexture = await createMarkerTexture(restaurant);
                    markerTexture.colorSpace = THREE.SRGBColorSpace;

                    const spriteMaterial = new THREE.SpriteMaterial({
                        map: markerTexture,
                        transparent: true,
                        depthTest: true,
                        depthWrite: false,
                    });

                    const sprite = new THREE.Sprite(spriteMaterial);
                    const surfacePosition = latLngToVector(latitude, longitude, earthRadius + 0.1);
                    const badgePosition = latLngToVector(latitude, longitude, earthRadius + 0.95);

                    sprite.position.copy(badgePosition);
                    sprite.scale.setScalar(usedFallback ? 1.28 : 1.4);
                    sprite.userData = {
                        restaurant,
                        baseScale: usedFallback ? 1.28 : 1.4,
                    };

                    const stem = new THREE.Line(
                        new THREE.BufferGeometry().setFromPoints([surfacePosition, badgePosition.clone().multiplyScalar(0.985)]),
                        new THREE.LineBasicMaterial({
                            color: usedFallback ? 0xfbbf24 : 0x7dd3fc,
                            transparent: true,
                            opacity: 0.7,
                        })
                    );

                    const accent = new THREE.Mesh(
                        new THREE.SphereGeometry(0.08, 16, 16),
                        new THREE.MeshBasicMaterial({
                            color: usedFallback ? 0xf59e0b : 0x34d399,
                        })
                    );
                    accent.position.copy(surfacePosition);

                    globeGroup.add(stem);
                    globeGroup.add(accent);
                    globeGroup.add(sprite);

                    markerSprites.push(sprite);
                    animatedMarkers.push(sprite);
                    surfaceAccents.push(accent);
                }

                function resolveCoordinates(restaurant, index, total) {
                    if (Number.isFinite(restaurant.latitude) && Number.isFinite(restaurant.longitude)) {
                        return {
                            latitude: restaurant.latitude,
                            longitude: restaurant.longitude,
                            usedFallback: false,
                        };
                    }

                    const safeTotal = Math.max(total, 1);
                    const goldenAngle = Math.PI * (3 - Math.sqrt(5));
                    const offsetIndex = index + 1;
                    const y = 1 - (offsetIndex / (safeTotal + 1)) * 2;
                    const radius = Math.sqrt(1 - y * y);
                    const theta = goldenAngle * offsetIndex;
                    const x = Math.cos(theta) * radius;
                    const z = Math.sin(theta) * radius;

                    return {
                        latitude: THREE.MathUtils.radToDeg(Math.asin(y)),
                        longitude: THREE.MathUtils.radToDeg(Math.atan2(z, x)),
                        usedFallback: true,
                    };
                }

                function latLngToVector(latitude, longitude, radius) {
                    const phi = THREE.MathUtils.degToRad(90 - latitude);
                    const theta = THREE.MathUtils.degToRad(longitude + 180);
                    const x = -radius * Math.sin(phi) * Math.cos(theta);
                    const y = radius * Math.cos(phi);
                    const z = radius * Math.sin(phi) * Math.sin(theta);

                    return new THREE.Vector3(x, y, z);
                }

                function createEarthTexture() {
                    const canvas = document.createElement('canvas');
                    canvas.width = 2048;
                    canvas.height = 1024;
                    const ctx = canvas.getContext('2d');

                    const oceanGradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
                    oceanGradient.addColorStop(0, '#103b5e');
                    oceanGradient.addColorStop(0.45, '#0e5378');
                    oceanGradient.addColorStop(1, '#09253f');
                    ctx.fillStyle = oceanGradient;
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    ctx.globalAlpha = 0.1;
                    for (let i = 0; i < 26; i++) {
                        ctx.fillStyle = i % 2 === 0 ? '#7dd3fc' : '#38bdf8';
                        ctx.fillRect(0, (canvas.height / 26) * i, canvas.width, 2);
                    }

                    ctx.globalAlpha = 0.85;
                    const landColors = ['#14532d', '#1f7a47', '#2ea36a', '#3cb179'];
                    const landMasses = [
                        [360, 300, 210, 135, -0.25],
                        [440, 420, 145, 170, 0.1],
                        [980, 300, 255, 165, 0.2],
                        [1135, 430, 115, 175, -0.1],
                        [1420, 420, 270, 170, 0.18],
                        [1670, 620, 130, 95, 0.22],
                        [1450, 210, 100, 68, -0.4],
                    ];

                    landMasses.forEach(([x, y, w, h, rotation], index) => {
                        ctx.save();
                        ctx.translate(x, y);
                        ctx.rotate(rotation);
                        ctx.fillStyle = landColors[index % landColors.length];
                        ctx.beginPath();
                        ctx.ellipse(0, 0, w, h, 0, 0, Math.PI * 2);
                        ctx.fill();
                        ctx.fillStyle = 'rgba(187, 247, 208, 0.16)';
                        ctx.beginPath();
                        ctx.ellipse(-w * 0.18, -h * 0.16, w * 0.45, h * 0.32, 0, 0, Math.PI * 2);
                        ctx.fill();
                        ctx.restore();
                    });

                    ctx.globalAlpha = 0.14;
                    for (let i = 0; i < 4500; i++) {
                        ctx.fillStyle = i % 3 === 0 ? '#d9f99d' : '#bbf7d0';
                        const x = Math.random() * canvas.width;
                        const y = Math.random() * canvas.height;
                        const size = Math.random() * 4.5;
                        ctx.beginPath();
                        ctx.arc(x, y, size, 0, Math.PI * 2);
                        ctx.fill();
                    }

                    ctx.globalAlpha = 0.18;
                    const glareGradient = ctx.createRadialGradient(470, 250, 30, 470, 250, 350);
                    glareGradient.addColorStop(0, 'rgba(255,255,255,0.55)');
                    glareGradient.addColorStop(1, 'rgba(255,255,255,0)');
                    ctx.fillStyle = glareGradient;
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    return new THREE.CanvasTexture(canvas);
                }

                function createCloudTexture() {
                    const canvas = document.createElement('canvas');
                    canvas.width = 1024;
                    canvas.height = 512;
                    const ctx = canvas.getContext('2d');

                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.globalAlpha = 0.24;

                    for (let i = 0; i < 220; i++) {
                        const x = Math.random() * canvas.width;
                        const y = Math.random() * canvas.height;
                        const width = 30 + Math.random() * 90;
                        const height = 12 + Math.random() * 26;
                        const gradient = ctx.createRadialGradient(x, y, 0, x, y, width);
                        gradient.addColorStop(0, 'rgba(255,255,255,0.95)');
                        gradient.addColorStop(1, 'rgba(255,255,255,0)');
                        ctx.fillStyle = gradient;
                        ctx.beginPath();
                        ctx.ellipse(x, y, width, height, Math.random(), 0, Math.PI * 2);
                        ctx.fill();
                    }

                    return new THREE.CanvasTexture(canvas);
                }

                function createStarsGeometry() {
                    const geometry = new THREE.BufferGeometry();
                    const starCount = 2200;
                    const positions = new Float32Array(starCount * 3);

                    for (let i = 0; i < starCount; i++) {
                        const radius = 45 + Math.random() * 70;
                        const theta = Math.random() * Math.PI * 2;
                        const phi = Math.acos(2 * Math.random() - 1);
                        positions[i * 3] = radius * Math.sin(phi) * Math.cos(theta);
                        positions[i * 3 + 1] = radius * Math.cos(phi);
                        positions[i * 3 + 2] = radius * Math.sin(phi) * Math.sin(theta);
                    }

                    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
                    return geometry;
                }

                async function createMarkerTexture(restaurant) {
                    const canvas = document.createElement('canvas');
                    canvas.width = 256;
                    canvas.height = 256;
                    const ctx = canvas.getContext('2d');

                    const badgeGradient = ctx.createLinearGradient(0, 0, 256, 256);
                    badgeGradient.addColorStop(0, 'rgba(12, 24, 37, 0.96)');
                    badgeGradient.addColorStop(1, 'rgba(17, 94, 89, 0.92)');
                    ctx.fillStyle = badgeGradient;
                    ctx.beginPath();
                    ctx.arc(128, 128, 112, 0, Math.PI * 2);
                    ctx.fill();

                    ctx.strokeStyle = restaurant.isOpen ? '#34d399' : '#fda4af';
                    ctx.lineWidth = 10;
                    ctx.beginPath();
                    ctx.arc(128, 128, 108, 0, Math.PI * 2);
                    ctx.stroke();

                    ctx.save();
                    ctx.beginPath();
                    ctx.arc(128, 118, 78, 0, Math.PI * 2);
                    ctx.closePath();
                    ctx.clip();

                    let imageDrawn = false;
                    if (restaurant.logo) {
                        try {
                            const image = await loadImage(restaurant.logo);
                            ctx.drawImage(image, 50, 40, 156, 156);
                            imageDrawn = true;
                        } catch (error) {
                            imageDrawn = false;
                        }
                    }

                    if (!imageDrawn) {
                        const fallbackGradient = ctx.createLinearGradient(50, 40, 206, 196);
                        fallbackGradient.addColorStop(0, '#38bdf8');
                        fallbackGradient.addColorStop(1, '#10b981');
                        ctx.fillStyle = fallbackGradient;
                        ctx.fillRect(50, 40, 156, 156);
                        ctx.fillStyle = 'rgba(255,255,255,0.92)';
                        ctx.font = '900 84px Outfit, sans-serif';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText((restaurant.name || '?').trim().charAt(0).toUpperCase(), 128, 120);
                    }

                    ctx.restore();

                    ctx.fillStyle = 'rgba(255,255,255,0.96)';
                    ctx.font = '900 20px Outfit, sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(truncateText(restaurant.name, 16), 128, 214);

                    return new THREE.CanvasTexture(canvas);
                }

                function loadImage(source) {
                    return new Promise((resolve, reject) => {
                        const image = new Image();
                        image.crossOrigin = 'anonymous';
                        image.onload = () => resolve(image);
                        image.onerror = reject;
                        image.src = source;
                    });
                }

                function truncateText(text, maxLength) {
                    if (text.length <= maxLength) {
                        return text;
                    }

                    return `${text.slice(0, maxLength - 3)}...`;
                }

                function animate() {
                    requestAnimationFrame(animate);

                    const elapsed = clock.getElapsedTime();
                    clouds.rotation.y += 0.00055;
                    starField.rotation.y -= 0.00015;

                    animatedMarkers.forEach((marker, index) => {
                        const hoverBoost = hoveredMarker === marker ? 1.17 : 1;
                        const pulse = 1 + Math.sin(elapsed * 2.2 + index * 0.6) * 0.06;
                        const scale = marker.userData.baseScale * pulse * hoverBoost;
                        marker.scale.set(scale, scale, scale);
                    });

                    surfaceAccents.forEach((accent, index) => {
                        const pulse = 0.75 + Math.sin(elapsed * 3 + index * 0.75) * 0.15;
                        accent.scale.setScalar(pulse);
                    });

                    controls.update();
                    if (hoveredMarker) {
                        showTooltip(hoveredMarker.userData.restaurant, hoveredMarker);
                    }

                    renderer.render(scene, camera);
                }
            }
        </script>
    @endpush
@endonce
