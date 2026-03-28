{{-- Interactive Map Partial --}}
<div class="map-section">
    <h3>🗺️ الخريطة التفاعلية</h3>
    <div id="test-map"></div>
    <div class="test-actions">
        <button id="start-simulation" class="btn btn-primary">▶️ بدء المحاكاة</button>
        <button id="stop-simulation" class="btn btn-secondary" disabled>⏹️ إيقاف</button>
        <button id="reset-simulation" class="btn btn-warning">🔄 إعادة تعيين</button>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    #test-map {
        width: 100%;
        height: 450px;
        border-radius: var(--radius-md);
        border: 2px solid var(--border-color);
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .bump-marker {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        border: 2px solid white;
    }

    .user-marker {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        padding: 8px 12px;
        border-radius: 50%;
        font-size: 16px;
        font-weight: bold;
        border: 3px solid white;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    .test-actions {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-top: 25px;
        justify-content: center;
    }

    .test-actions .btn {
        flex: 1;
        min-width: 160px;
        padding: 12px 20px;
        font-size: 16px;
        font-weight: 600;
        border-radius: var(--radius-lg);
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .test-actions .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .test-actions .btn:active {
        transform: translateY(0);
    }

    /* Loading animation for simulation */
    .simulating {
        position: relative;
    }

    .simulating::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(59, 130, 246, 0.1);
        border-radius: var(--radius-lg);
        animation: loading 1.5s ease-in-out infinite;
        pointer-events: none;
    }

    @keyframes loading {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 0.7; }
    }

    @media (max-width: 768px) {
        #test-map {
            height: 350px;
        }

        .test-actions {
            flex-direction: column;
        }

        .test-actions .btn {
            min-width: auto;
            width: 100%;
        }
    }
</style>

<script>
let testMap = null;
let userMarker = null;
let bumpMarkers = [];
let simulationInterval = null;
let isSimulating = false;
let totalAlertsGenerated = 0;
let simulationStartTime = null;
let simulationTotalTime = 0;

const bumps = @json($bumps);

function initTestMap() {
    if (testMap) return; // avoid double initialization

    testMap = L.map('test-map', {
        center: [24.7136, 46.6753],
        zoom: 15,
        minZoom: 5,
        maxZoom: 20,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(testMap);

    // Add bump markers
    bumps.forEach(bump => {
        const marker = L.divIcon({
            className: 'bump-marker',
            html: '📍',
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        });

        const mapMarker = L.marker([bump.latitude, bump.longitude], { icon: marker }).addTo(testMap);

        mapMarker.bindPopup(`
            <strong>مطب #${bump.id}</strong><br>
            ${bump.description || 'بدون وصف'}<br>
            النوع: ${bump.type}<br>
            الثقة: ${getConfidenceText(bump.confidence_level)}
        `);

        bumpMarkers.push(mapMarker);
    });

    // Add user marker
    updateUserMarker();

    // Ensure Leaflet correctly computes sizes when inside responsive containers
    setTimeout(() => {
        try { testMap.invalidateSize(); } catch (e) { /* ignore */ }
    }, 200);
}

function updateUserMarker() {
    const lat = parseFloat(document.getElementById('user-lat').value);
    const lng = parseFloat(document.getElementById('user-lng').value);

    if (userMarker) {
        testMap.removeLayer(userMarker);
    }

    userMarker = L.marker([lat, lng], {
        icon: L.divIcon({
            className: 'user-marker',
            html: '👤',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        }),
        draggable: true
    }).addTo(testMap);

    userMarker.on('dragend', () => {
        const pos = userMarker.getLatLng();
        document.getElementById('user-lat').value = pos.lat.toFixed(6);
        document.getElementById('user-lng').value = pos.lng.toFixed(6);
        checkNearbyBumps();
    });

    testMap.setView([lat, lng], testMap.getZoom());
}

function getConfidenceText(level) {
    const levels = {
        'low': 'منخفضة (35%)',
        'medium': 'متوسطة (65%)',
        'high': 'عالية (90%)'
    };
    return levels[level] || level;
}

function calculateDistance(lat1, lng1, lat2, lng2) {
    const R = 6371; // Earth's radius in km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLng/2) * Math.sin(dLng/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c * 1000; // Convert to meters
}

function checkNearbyBumps() {
    const userLat = parseFloat(document.getElementById('user-lat').value);
    const userLng = parseFloat(document.getElementById('user-lng').value);
    const radius = parseInt(document.getElementById('detection-radius').value);
    const selectedBumpId = document.getElementById('selected-bump').value;

    let nearbyBumps = [];

    if (selectedBumpId) {
        // Test specific bump
        const selectedBump = bumps.find(b => b.id == selectedBumpId);
        if (selectedBump) {
            const distance = calculateDistance(userLat, userLng, selectedBump.latitude, selectedBump.longitude);
            if (distance <= radius) {
                nearbyBumps.push({ ...selectedBump, distance: Math.round(distance) });
            }
        }
    } else {
        // Check all bumps
        bumps.forEach(bump => {
            const distance = calculateDistance(userLat, userLng, bump.latitude, bump.longitude);
            if (distance <= radius) {
                nearbyBumps.push({ ...bump, distance: Math.round(distance) });
            }
        });
    }

    // Sort by distance
    nearbyBumps.sort((a, b) => a.distance - b.distance);

    // Generate alerts
    generateAlerts(nearbyBumps);
}

function generateAlerts(nearbyBumps) {
    const alertsContainer = document.getElementById('alerts-container');
    const alertsCount = document.getElementById('alerts-count');
    const userSpeed = parseInt(document.getElementById('user-speed').value);

    // Clear old alerts except the welcome message
    const oldAlerts = alertsContainer.querySelectorAll('.alert-item:not(:first-child)');
    oldAlerts.forEach(alert => alert.remove());

    // Update alerts count
    alertsCount.textContent = nearbyBumps.length;

    // Update total alerts generated
    totalAlertsGenerated += nearbyBumps.length;
    document.getElementById('total-alerts').textContent = totalAlertsGenerated;

    if (nearbyBumps.length === 0) {
        addAlert('لا توجد مطبات قريبة في النطاق المحدد', 'info');
        return;
    }

    nearbyBumps.forEach((bump, index) => {
        let alertType = 'warning';
        let alertIcon = '⚠️';
        let alertMessage = '';
        let alertDetails = '';

        if (bump.distance <= 50) {
            alertType = 'danger';
            alertIcon = '🚨';
            alertMessage = `مطب خطير على بعد ${bump.distance} متر!`;
            alertDetails = `توقف فوراً! ${bump.description || 'مطب سرعة'}`;
        } else if (bump.distance <= 100) {
            alertType = 'warning';
            alertIcon = '⚠️';
            alertMessage = `تحذير: مطب على بعد ${bump.distance} متر`;
            alertDetails = `خفض سرعتك. ${bump.description || 'مطب سرعة'}`;
        } else {
            alertType = 'info';
            alertIcon = 'ℹ️';
            alertMessage = `مطب على بعد ${bump.distance} متر`;
            alertDetails = `كن حذراً. ${bump.description || 'مطب سرعة'}`;
        }

        // Speed-based warnings
        if (userSpeed > 60 && bump.distance <= 200) {
            alertDetails += ` | سرعتك عالية (${userSpeed} كم/ساعة)`;
        }

        // Add delay for animation effect
        setTimeout(() => {
            addAlert(`${alertMessage}<br><small>${alertDetails}</small>`, alertType, alertIcon);
        }, index * 100);
    });
}

function addAlert(message, type, icon = 'ℹ️') {
    const alertsContainer = document.getElementById('alerts-container');
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert-item alert-${type}`;
    alertDiv.innerHTML = `
        <span class="alert-icon">${icon}</span>
        <div>${message}</div>
    `;

    // Insert after the welcome message
    const welcomeMessage = alertsContainer.querySelector('.alert-item');
    if (welcomeMessage) {
        welcomeMessage.insertAdjacentElement('afterend', alertDiv);
    } else {
        alertsContainer.appendChild(alertDiv);
    }

    // Auto scroll to bottom
    alertsContainer.scrollTop = alertsContainer.scrollHeight;
}

function startSimulation() {
    if (isSimulating) return;

    isSimulating = true;
    document.getElementById('start-simulation').disabled = true;
    document.getElementById('stop-simulation').disabled = false;

    // Start timing
    simulationStartTime = Date.now();

    // Add simulating class for visual effect
    document.querySelector('.simulation-area').classList.add('simulating');

    addAlert('بدء المحاكاة...', 'info');

    simulationInterval = setInterval(() => {
        // Simulate movement (small random changes)
        const lat = parseFloat(document.getElementById('user-lat').value);
        const lng = parseFloat(document.getElementById('user-lng').value);

        const newLat = lat + (Math.random() - 0.5) * 0.001;
        const newLng = lng + (Math.random() - 0.5) * 0.001;

        document.getElementById('user-lat').value = newLat.toFixed(6);
        document.getElementById('user-lng').value = newLng.toFixed(6);

        updateUserMarker();
        checkNearbyBumps();
    }, 2000); // Update every 2 seconds
}

function stopSimulation() {
    if (!isSimulating) return;

    isSimulating = false;
    document.getElementById('start-simulation').disabled = false;
    document.getElementById('stop-simulation').disabled = true;

    // Calculate and update simulation time
    if (simulationStartTime) {
        const currentTime = Date.now();
        const sessionTime = Math.floor((currentTime - simulationStartTime) / 1000);
        simulationTotalTime += sessionTime;
        document.getElementById('simulation-time').textContent = simulationTotalTime;
        simulationStartTime = null;
    }

    // Remove simulating class
    document.querySelector('.simulation-area').classList.remove('simulating');

    if (simulationInterval) {
        clearInterval(simulationInterval);
        simulationInterval = null;
    }

    addAlert('تم إيقاف المحاكاة', 'info');
}

function resetSimulation() {
    stopSimulation();

    // Reset to default values
    document.getElementById('user-lat').value = '24.713600';
    document.getElementById('user-lng').value = '46.675300';
    document.getElementById('user-speed').value = '60';
    document.getElementById('detection-radius').value = '100';
    document.getElementById('selected-bump').value = '';

    // Reset statistics
    totalAlertsGenerated = 0;
    simulationTotalTime = 0;
    document.getElementById('total-alerts').textContent = '0';
    document.getElementById('simulation-time').textContent = '0';

    updateUserMarker();

    // Clear alerts except welcome message
    const alertsContainer = document.getElementById('alerts-container');
    const oldAlerts = alertsContainer.querySelectorAll('.alert-item:not(:first-child)');
    oldAlerts.forEach(alert => alert.remove());

    addAlert('تم إعادة تعيين المحاكاة والإحصائيات', 'info');
}

// Initialize map when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    initTestMap();

    // Event listeners
    document.getElementById('user-lat').addEventListener('input', updateUserMarker);
    document.getElementById('user-lng').addEventListener('input', updateUserMarker);
    document.getElementById('user-speed').addEventListener('input', checkNearbyBumps);
    document.getElementById('detection-radius').addEventListener('input', checkNearbyBumps);
    document.getElementById('selected-bump').addEventListener('change', checkNearbyBumps);

    document.getElementById('start-simulation').addEventListener('click', startSimulation);
    document.getElementById('stop-simulation').addEventListener('click', stopSimulation);
    document.getElementById('reset-simulation').addEventListener('click', resetSimulation);

    // Initial check
    checkNearbyBumps();
});
</script>