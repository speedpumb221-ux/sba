/**
 * Speed & Prediction Indicators Manager
 * مدير مؤشرات السرعة والتنبؤ الذكي
 */

class SpeedIndicatorsManager {
    constructor() {
        this.currentSpeed = 0;
        this.maxSpeed = 120; // km/h
        this.speedHistory = [];
        this.maxHistorySize = 60;
        this.updateInterval = 500; // ms
        this.isRunning = false;

        this.initializeIndicators();
    }

    /**
     * Initialize Indicators
     */
    initializeIndicators() {
        this.createSpeedIndicator();
        this.createPredictionIndicator();
        this.createMetricsDisplay();
        this.startUpdating();
    }

    /**
     * Create Speed Indicator Widget
     */
    createSpeedIndicator() {
        const container = document.createElement('div');
        container.id = 'speed-indicator-widget';
        container.className = 'speed-indicator';
        container.innerHTML = `
            <div class="speed-indicator-header">
                <span class="speed-indicator-icon">🚗</span>
                <span>السرعة الحالية</span>
            </div>
            <div class="speed-indicator-value" id="speed-value">0</div>
            <div class="speed-indicator-unit">كم/س</div>
            <div class="speed-bar">
                <div class="speed-bar-fill" id="speed-bar-fill" style="width: 0%"></div>
            </div>
            <div class="speed-status safe" id="speed-status">
                <span>✓</span>
                <span>آمن</span>
            </div>
        `;

        document.body.appendChild(container);
    }

    /**
     * Create Prediction Indicator Widget
     */
    createPredictionIndicator() {
        const container = document.createElement('div');
        container.id = 'prediction-indicator-widget';
        container.className = 'prediction-indicator';
        container.innerHTML = `
            <div class="prediction-indicator-header">
                <span class="prediction-indicator-icon">🎯</span>
                <span>التنبؤ الذكي</span>
            </div>
            <div class="prediction-status">
                <div class="prediction-item">
                    <span class="prediction-item-label">
                        <span>📊</span>
                        <span>التنبؤات</span>
                    </span>
                    <span class="prediction-item-value" id="prediction-count">0</span>
                </div>
                <div class="prediction-item">
                    <span class="prediction-item-label">
                        <span>🎯</span>
                        <span>الثقة</span>
                    </span>
                    <span class="prediction-item-value" id="prediction-confidence">0%</span>
                </div>
                <div class="prediction-item">
                    <span class="prediction-item-label">
                        <span>⚡</span>
                        <span>الاهتزاز</span>
                    </span>
                    <span class="prediction-item-value" id="prediction-vibration">0</span>
                </div>
            </div>
            <div class="confidence-meter">
                <div class="confidence-meter-label">مستوى الثقة الكلي</div>
                <div class="confidence-meter-bar">
                    <div class="confidence-meter-fill" id="confidence-fill" style="width: 0%"></div>
                </div>
                <div class="confidence-meter-text" id="confidence-text">0%</div>
            </div>
        `;

        document.body.appendChild(container);
    }

    /**
     * Create Metrics Display
     */
    createMetricsDisplay() {
        const container = document.createElement('div');
        container.id = 'metrics-display-widget';
        container.className = 'metrics-display';
        container.innerHTML = `
            <div class="metrics-item">
                <span class="metrics-label">📍 الموقع</span>
                <span class="metrics-value" id="metrics-location">--</span>
            </div>
            <div class="metrics-item">
                <span class="metrics-label">📈 التسارع</span>
                <span class="metrics-value" id="metrics-acceleration">0 G</span>
            </div>
            <div class="metrics-item">
                <span class="metrics-label">⏱️ الوقت</span>
                <span class="metrics-value" id="metrics-time">--:--</span>
            </div>
            <div class="metrics-item">
                <span class="metrics-label">🎯 الدقة</span>
                <span class="metrics-value" id="metrics-accuracy">--</span>
            </div>
        `;

        document.body.appendChild(container);
    }

    /**
     * Start Updating
     */
    startUpdating() {
        if (this.isRunning) return;

        this.isRunning = true;

        // Update speed from geolocation
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(
                (position) => {
                    this.currentSpeed = (position.coords.speed || 0) * 3.6; // m/s to km/h
                    this.updateSpeedIndicator();
                    this.updateMetrics(position);
                },
                (error) => console.warn('Geolocation error:', error),
                {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                }
            );
        }

        // Update prediction indicators
        setInterval(() => {
            this.updatePredictionIndicator();
        }, this.updateInterval);

        // Update time
        setInterval(() => {
            this.updateTime();
        }, 1000);
    }

    /**
     * Update Speed Indicator
     */
    updateSpeedIndicator() {
        const speedValue = document.getElementById('speed-value');
        const speedBarFill = document.getElementById('speed-bar-fill');
        const speedStatus = document.getElementById('speed-status');

        if (speedValue) {
            speedValue.textContent = Math.round(this.currentSpeed);
            speedValue.classList.add('fluctuating');
            setTimeout(() => speedValue.classList.remove('fluctuating'), 500);
        }

        // Update speed bar
        const percentage = Math.min((this.currentSpeed / this.maxSpeed) * 100, 100);
        if (speedBarFill) {
            speedBarFill.style.width = percentage + '%';
        }

        // Update status
        let status = 'safe';
        let statusText = '✓ آمن';
        let statusColor = 'safe';

        if (this.currentSpeed > 80) {
            status = 'high';
            statusText = '⚠️ سرعة عالية';
            statusColor = 'high';
        } else if (this.currentSpeed > 50) {
            status = 'moderate';
            statusText = '⚠️ سرعة معتدلة';
            statusColor = 'moderate';
        }

        if (speedStatus) {
            speedStatus.className = `speed-status ${statusColor}`;
            speedStatus.innerHTML = `<span>${statusText}</span>`;
        }

        // Add to history
        this.speedHistory.push({
            speed: this.currentSpeed,
            timestamp: Date.now()
        });

        if (this.speedHistory.length > this.maxHistorySize) {
            this.speedHistory.shift();
        }
    }

    /**
     * Update Prediction Indicator
     */
    updatePredictionIndicator() {
        if (!window.bumpPredictor) return;

        const predictions = window.bumpPredictor.getPredictions();
        const recentPredictions = window.bumpPredictor.getRecentPredictions(5); // Last 5 minutes
        const stats = window.bumpPredictor.getStatistics();

        const countElement = document.getElementById('prediction-count');
        const confidenceElement = document.getElementById('prediction-confidence');
        const vibrationElement = document.getElementById('prediction-vibration');
        const confidenceFill = document.getElementById('confidence-fill');
        const confidenceText = document.getElementById('confidence-text');

        if (countElement) {
            countElement.textContent = recentPredictions.length;
        }

        if (confidenceElement && stats.averageConfidence) {
            const confidence = Math.round(stats.averageConfidence);
            confidenceElement.textContent = confidence + '%';
        }

        if (vibrationElement && window.bumpPredictor.accelerometerData.length > 0) {
            const lastData = window.bumpPredictor.accelerometerData[window.bumpPredictor.accelerometerData.length - 1];
            const vibration = Math.round(lastData.magnitude * 10) / 10;
            vibrationElement.textContent = vibration.toFixed(1);
        }

        // Update confidence meter
        const avgConfidence = parseFloat(stats.averageConfidence) || 0;
        if (confidenceFill) {
            confidenceFill.style.width = avgConfidence + '%';
        }
        if (confidenceText) {
            confidenceText.textContent = Math.round(avgConfidence) + '%';
        }

        // Add alert class if high confidence
        const widget = document.getElementById('prediction-indicator-widget');
        if (widget && avgConfidence > 70) {
            widget.classList.add('alert');
        } else if (widget) {
            widget.classList.remove('alert');
        }
    }

    /**
     * Update Metrics
     */
    updateMetrics(position) {
        const locationElement = document.getElementById('metrics-location');
        const accuracyElement = document.getElementById('metrics-accuracy');

        if (locationElement) {
            const lat = position.coords.latitude.toFixed(4);
            const lng = position.coords.longitude.toFixed(4);
            locationElement.textContent = `${lat}, ${lng}`;
        }

        if (accuracyElement) {
            const accuracy = Math.round(position.coords.accuracy);
            accuracyElement.textContent = accuracy + 'م';
        }
    }

    /**
     * Update Acceleration Metrics
     */
    updateAccelerationMetrics() {
        if (!window.bumpPredictor || window.bumpPredictor.accelerometerData.length === 0) {
            return;
        }

        const lastData = window.bumpPredictor.accelerometerData[window.bumpPredictor.accelerometerData.length - 1];
        const accelerationElement = document.getElementById('metrics-acceleration');

        if (accelerationElement) {
            const magnitude = (lastData.magnitude / 9.8).toFixed(2); // Convert to G-force
            accelerationElement.textContent = magnitude + ' G';
        }
    }

    /**
     * Update Time
     */
    updateTime() {
        const timeElement = document.getElementById('metrics-time');
        if (timeElement) {
            const now = new Date();
            const time = now.toLocaleTimeString('ar-SA', {
                hour: '2-digit',
                minute: '2-digit'
            });
            timeElement.textContent = time;
        }
    }

    /**
     * Show Speed Alert
     */
    showSpeedAlert(speed, limit = 80) {
        if (speed <= limit) return;

        const alert = document.createElement('div');
        alert.className = 'alert-notification';
        alert.innerHTML = `
            <div class="alert-notification-icon">⚠️</div>
            <div class="alert-notification-title">تحذير السرعة</div>
            <div class="alert-notification-message">أنت تتجاوز الحد الآمن للسرعة</div>
            <div class="alert-notification-details">
                <div class="alert-detail-item">
                    <div class="alert-detail-label">السرعة الحالية</div>
                    <div class="alert-detail-value">${Math.round(speed)} كم/س</div>
                </div>
                <div class="alert-detail-item">
                    <div class="alert-detail-label">الحد الآمن</div>
                    <div class="alert-detail-value">${limit} كم/س</div>
                </div>
            </div>
        `;

        document.body.appendChild(alert);

        setTimeout(() => {
            alert.style.animation = 'alertFadeOut 0.5s ease-out';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    }

    /**
     * Get Speed History
     */
    getSpeedHistory() {
        return this.speedHistory;
    }

    /**
     * Get Average Speed
     */
    getAverageSpeed() {
        if (this.speedHistory.length === 0) return 0;
        const total = this.speedHistory.reduce((sum, item) => sum + item.speed, 0);
        return total / this.speedHistory.length;
    }

    /**
     * Get Max Speed
     */
    getMaxSpeed() {
        if (this.speedHistory.length === 0) return 0;
        return Math.max(...this.speedHistory.map(item => item.speed));
    }

    /**
     * Stop Updating
     */
    stopUpdating() {
        this.isRunning = false;
    }
}

// Create global instance
window.speedIndicators = new SpeedIndicatorsManager();

/**
 * Initialize on DOM Ready
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('Speed & Prediction Indicators initialized');

    // Add CSS link if not already added
    if (!document.querySelector('link[href*="speed-indicators.css"]')) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = '/css/speed-indicators.css';
        document.head.appendChild(link);
    }

    // Start updating
    window.speedIndicators.startUpdating();
});

/**
 * Real-time Dashboard Updates
 */
class DashboardUpdater {
    constructor() {
        this.updateInterval = 2000; // 2 seconds
        this.isRunning = false;
    }

    /**
     * Start Updates
     */
    start() {
        if (this.isRunning) return;

        this.isRunning = true;

        setInterval(() => {
            this.updateDashboard();
        }, this.updateInterval);
    }

    /**
     * Update Dashboard
     */
    updateDashboard() {
        if (window.bumpPredictor) {
            const stats = window.bumpPredictor.getStatistics();
            this.updateDashboardStats(stats);
        }

        if (window.speedIndicators) {
            this.updateSpeedStats();
        }
    }

    /**
     * Update Dashboard Statistics
     */
    updateDashboardStats(stats) {
        // Update prediction count
        const predictionCountEl = document.querySelector('[data-stat="predictions"]');
        if (predictionCountEl) {
            predictionCountEl.textContent = stats.total;
        }

        // Update success rate
        const successRateEl = document.querySelector('[data-stat="success-rate"]');
        if (successRateEl) {
            successRateEl.textContent = stats.successRate + '%';
        }

        // Update average confidence
        const avgConfidenceEl = document.querySelector('[data-stat="avg-confidence"]');
        if (avgConfidenceEl) {
            avgConfidenceEl.textContent = stats.averageConfidence + '%';
        }
    }

    /**
     * Update Speed Statistics
     */
    updateSpeedStats() {
        const avgSpeed = window.speedIndicators.getAverageSpeed();
        const maxSpeed = window.speedIndicators.getMaxSpeed();

        const avgSpeedEl = document.querySelector('[data-stat="avg-speed"]');
        if (avgSpeedEl) {
            avgSpeedEl.textContent = Math.round(avgSpeed) + ' كم/س';
        }

        const maxSpeedEl = document.querySelector('[data-stat="max-speed"]');
        if (maxSpeedEl) {
            maxSpeedEl.textContent = Math.round(maxSpeed) + ' كم/س';
        }
    }

    /**
     * Stop Updates
     */
    stop() {
        this.isRunning = false;
    }
}

window.dashboardUpdater = new DashboardUpdater();
