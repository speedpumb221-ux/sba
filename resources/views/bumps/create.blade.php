@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/leaflet-markercluster/MarkerCluster.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/leaflet-markercluster/MarkerCluster.Default.css') }}" />
<style>
    #bump-create-map {
        width: 100%;
        height: 350px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: var(--radius-lg);
        margin-bottom: 1rem;
    }

    .location-action-group {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .location-action-group .btn {
        flex: 1;
        min-width: 135px;
    }

    .bump-marker-divicon {
        font-size: 22px;
        line-height: 1;
    }

    .bump-marker-icon {
        display: inline-block;
        transform: translateX(-50%) translateY(-50%);
        text-shadow: 0 1px 2px rgba(0,0,0,.35);
    }

    /* Alert message animations */
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    .alert-message {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 14px;
        line-height: 1.4;
    }
</style>
@endsection

@section('content')
<div class="container">
    <h1 class="mb-4">إضافة مطب جديد</h1>

    <div id="bump-create-map"></div>

    <div class="location-action-group">
        <button id="use-current-location" type="button" class="btn btn-success">📍 استخدام موقعي الحالي</button>
        <button id="select-map-location" type="button" class="btn btn-info">🗺️ انقر على الخريطة</button>
        <button id="clear-location" type="button" class="btn btn-secondary">✖️ مسح الموقع</button>
    </div>

    <p class="text-muted">اضغط على الخريطة لإختيار موقع المطبة أو استخدم زر الموقع الحالي ثم اضغط حفظ.</p>

    <form id="bump-create-form" method="POST" action="{{ route('bumps.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="latitude">خط العرض</label>
                    <input type="text" class="form-control" id="latitude" name="latitude" readonly required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="longitude">خط الطول</label>
                    <input type="text" class="form-control" id="longitude" name="longitude" readonly required>
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="description">الوصف</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
        </div>

        <div class="form-group mb-3">
            <label for="type">النوع</label>
            <select class="form-control" id="type" name="type" required>
                <option value="">اختر نوع المطبة</option>
                <option value="normal">عادي</option>
                <option value="speed_bump">مطب حقيقي</option>
                <option value="hump">منحدر</option>
                <option value="bump">مطب</option>
                <option value="rumble_strip">شريط اهتزازي</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">إضافة المطبة</button>
    </form>
</div>

@section('scripts')
<script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
<script src="{{ asset('vendor/leaflet-markercluster/leaflet.markercluster.js') }}"></script>
<script>
    let createMap = null;
    let createMarker = null;
    let mapSelectorEnabled = true; // always allow click-to-set unless explicitly disabled

    function setLocationInputs(lat, lng) {
        document.getElementById('latitude').value = parseFloat(lat).toFixed(6);
        document.getElementById('longitude').value = parseFloat(lng).toFixed(6);
    }

    const bumpMarkerIcon = L.divIcon({
        className: 'bump-marker-divicon',
        html: '<span class="bump-marker-icon">📍</span>',
        iconSize: [30, 30],
        iconAnchor: [15, 30]
    });

    function updateMarker(lat, lng) {
        if (!createMap) return;

        if (createMarker) {
            createMap.removeLayer(createMarker);
        }

        createMarker = L.marker([lat, lng], {
            draggable: true,
            icon: bumpMarkerIcon
        }).addTo(createMap);

        createMarker.on('dragend', () => {
            const pos = createMarker.getLatLng();
            setLocationInputs(pos.lat, pos.lng);
        });

        createMap.setView([lat, lng], 17);
        setLocationInputs(lat, lng);
    }

    function initCreateMap() {
        createMap = L.map('bump-create-map', {
            center: [24.7136, 46.6753],
            zoom: 13,
            minZoom: 5,
            maxZoom: 20,
            dragging: true,
            tap: true,
            touchZoom: true,
            doubleClickZoom: true,
            boxZoom: true,
            keyboard: true,
            scrollWheelZoom: true,
            zoomControl: true,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(createMap);

        createMap.on('click', function(e) {
            updateMarker(e.latlng.lat, e.latlng.lng);
        });

        const existingLat = document.getElementById('latitude').value;
        const existingLng = document.getElementById('longitude').value;
        if (existingLat && existingLng) {
            updateMarker(existingLat, existingLng);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initCreateMap();

        document.getElementById('use-current-location').addEventListener('click', () => {
            if (!navigator.geolocation) {
                alert('الموقع غير مدعوم في هذا المتصفح');
                return;
            }

            navigator.geolocation.getCurrentPosition((position) => {
                updateMarker(position.coords.latitude, position.coords.longitude);
                mapSelectorEnabled = false;
                document.getElementById('select-map-location').classList.remove('active');
            }, () => {
                alert('تعذر الحصول على موقعك الحالي');
            }, { enableHighAccuracy: true, timeout: 10000 });
        });

        document.getElementById('select-map-location').addEventListener('click', () => {
            mapSelectorEnabled = !mapSelectorEnabled;
            document.getElementById('select-map-location').classList.toggle('active', mapSelectorEnabled);
            document.getElementById('select-map-location').textContent = mapSelectorEnabled ? '🗺️ اضغط على الخريطة لاختيار موقع' : '🗺️ انقر على الخريطة';
        });

        document.getElementById('clear-location').addEventListener('click', () => {
            if (createMarker) {
                createMap.removeLayer(createMarker);
                createMarker = null;
            }
            mapSelectorEnabled = false;
            document.getElementById('select-map-location').classList.remove('active');
            document.getElementById('select-map-location').textContent = '🗺️ انقر على الخريطة';
            document.getElementById('latitude').value = '';
            document.getElementById('longitude').value = '';
        });

        document.getElementById('bump-create-form').addEventListener('submit', function(event) {
            if (!document.getElementById('latitude').value || !document.getElementById('longitude').value) {
                event.preventDefault();
                alert('يرجى اختيار موقع على الخريطة أو استخدام موقعك الحالي قبل الإرسال');
            }
        });

        // Handle form submission with AJAX for better UX
        document.getElementById('bump-create-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            if (!document.getElementById('latitude').value || !document.getElementById('longitude').value) {
                showMessage('يرجى اختيار موقع على الخريطة أو استخدام موقعك الحالي قبل الإرسال', 'error');
                return;
            }

            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;

            // Show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'جاري الإضافة...';

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message || 'تم إضافة المطب بنجاح', 'success');
                    // Reset form
                    this.reset();
                    if (createMarker) {
                        createMap.removeLayer(createMarker);
                        createMarker = null;
                    }
                    // Optionally redirect to map or bumps list
                    setTimeout(() => {
                        window.location.href = '{{ route("bumps.index") }}';
                    }, 2000);
                } else {
                    showMessage('حدث خطأ أثناء إضافة المطب', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('حدث خطأ في الاتصال. يرجى المحاولة مرة أخرى.', 'error');
            })
            .finally(() => {
                // Reset button state
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            });
        });

        function showMessage(message, type = 'info') {
            // Remove existing messages
            const existingMessages = document.querySelectorAll('.alert-message');
            existingMessages.forEach(msg => msg.remove());

            // Create message element
            const messageDiv = document.createElement('div');
            messageDiv.className = `alert-message alert-${type}`;
            messageDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                font-weight: bold;
                z-index: 10000;
                max-width: 400px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                animation: slideIn 0.3s ease-out;
            `;

            // Set colors based on type
            const colors = {
                success: '#10b981',
                error: '#ef4444',
                warning: '#f59e0b',
                info: '#3b82f6'
            };

            messageDiv.style.backgroundColor = colors[type] || colors.info;
            messageDiv.textContent = message;

            // Add close button
            const closeBtn = document.createElement('span');
            closeBtn.textContent = '×';
            closeBtn.style.cssText = `
                float: left;
                margin-left: 10px;
                cursor: pointer;
                font-size: 20px;
                line-height: 1;
            `;
            closeBtn.onclick = () => messageDiv.remove();
            messageDiv.insertBefore(closeBtn, messageDiv.firstChild);

            document.body.appendChild(messageDiv);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.style.animation = 'slideOut 0.3s ease-out';
                    setTimeout(() => messageDiv.remove(), 300);
                }
            }, 5000);
        }
    });
</script>
@endsection

@endsection

