/**
 * Smart Bump Prediction System
 * نظام التنبؤ الذكي بالمطبات باستخدام بيانات الحساسات
 */

class SmartBumpPredictor {
    constructor() {
        this.isMonitoring = false;
        this.accelerometerData = [];
        this.speedData = [];
        this.predictions = [];
        this.maxDataPoints = 100;
        
        // Thresholds for bump detection
        this.THRESHOLDS = {
            accelerationX: 0.5,      // G-force threshold
            accelerationY: 0.5,
            accelerationZ: 1.2,      // Vertical acceleration (most important)
            speedDrop: 5,            // km/h drop threshold
            vibrationMagnitude: 0.8  // Combined vibration threshold
        };

        // Bump detection parameters
        this.BUMP_DETECTION = {
            minDuration: 100,        // ms - minimum bump duration
            maxDuration: 1000,       // ms - maximum bump duration
            cooldown: 2000,          // ms - cooldown between detections
            confidenceThreshold: 0.6 // 0-1 confidence score
        };

        this.lastPredictionTime = 0;
        this.currentBumpEvent = null;
        this.confidenceScores = [];

        this.initializeAccelerometer();
        this.initializeSpeedTracking();
    }

    /**
     * Initialize Accelerometer
     */
    initializeAccelerometer() {
        if (!window.DeviceMotionEvent) {
            console.warn('DeviceMotionEvent not supported');
            return;
        }

        // Request permission for iOS 13+
        if (typeof DeviceMotionEvent !== 'undefined' && typeof DeviceMotionEvent.requestPermission === 'function') {
            DeviceMotionEvent.requestPermission()
                .then(permissionState => {
                    if (permissionState === 'granted') {
                        window.addEventListener('devicemotion', this.handleDeviceMotion.bind(this));
                    }
                })
                .catch(console.error);
        } else {
            // Non-iOS 13 devices
            window.addEventListener('devicemotion', this.handleDeviceMotion.bind(this));
        }
    }

    /**
     * Initialize Speed Tracking
     */
    initializeSpeedTracking() {
        if (!navigator.geolocation) {
            console.warn('Geolocation not supported');
            return;
        }

        navigator.geolocation.watchPosition(
            (position) => {
                const speed = position.coords.speed || 0;
                this.speedData.push({
                    speed: speed * 3.6, // Convert m/s to km/h
                    timestamp: Date.now(),
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                });

                // Keep only recent data
                if (this.speedData.length > this.maxDataPoints) {
                    this.speedData.shift();
                }
            },
            (error) => console.warn('Geolocation error:', error),
            {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            }
        );
    }

    /**
     * Handle Device Motion
     */
    handleDeviceMotion(event) {
        if (!this.isMonitoring) return;

        const accel = event.accelerationIncludingGravity;
        
        if (!accel) return;

        const dataPoint = {
            x: accel.x || 0,
            y: accel.y || 0,
            z: accel.z || 0,
            timestamp: Date.now(),
            magnitude: Math.sqrt(
                (accel.x || 0) ** 2 + 
                (accel.y || 0) ** 2 + 
                (accel.z || 0) ** 2
            )
        };

        this.accelerometerData.push(dataPoint);

        // Keep only recent data
        if (this.accelerometerData.length > this.maxDataPoints) {
            this.accelerometerData.shift();
        }

        // Analyze for bump detection
        this.analyzeBumpPattern();
    }

    /**
     * Analyze Bump Pattern
     */
    analyzeBumpPattern() {
        if (this.accelerometerData.length < 10) return;

        const now = Date.now();
        
        // Check cooldown
        if (now - this.lastPredictionTime < this.BUMP_DETECTION.cooldown) {
            return;
        }

        // Get recent data
        const recentData = this.accelerometerData.slice(-20);
        
        // Calculate statistics
        const stats = this.calculateStatistics(recentData);
        
        // Check for bump pattern
        const bumpScore = this.calculateBumpScore(stats);
        
        if (bumpScore >= this.BUMP_DETECTION.confidenceThreshold) {
            this.detectBump(stats, bumpScore);
        }
    }

    /**
     * Calculate Statistics
     */
    calculateStatistics(data) {
        const magnitudes = data.map(d => d.magnitude);
        const zValues = data.map(d => Math.abs(d.z));
        const xValues = data.map(d => Math.abs(d.x));
        const yValues = data.map(d => Math.abs(d.y));

        const mean = (arr) => arr.reduce((a, b) => a + b, 0) / arr.length;
        const std = (arr) => {
            const m = mean(arr);
            return Math.sqrt(arr.reduce((a, b) => a + (b - m) ** 2, 0) / arr.length);
        };
        const max = (arr) => Math.max(...arr);
        const min = (arr) => Math.min(...arr);

        return {
            magnitudeMean: mean(magnitudes),
            magnitudeStd: std(magnitudes),
            magnitudeMax: max(magnitudes),
            magnitudeMin: min(magnitudes),
            zMean: mean(zValues),
            zMax: max(zValues),
            xMax: max(xValues),
            yMax: max(yValues),
            duration: data[data.length - 1].timestamp - data[0].timestamp,
            peakCount: this.countPeaks(magnitudes),
            energyContent: magnitudes.reduce((a, b) => a + b ** 2, 0)
        };
    }

    /**
     * Count Peaks in Data
     */
    countPeaks(data) {
        let peaks = 0;
        const threshold = 0.7;
        
        for (let i = 1; i < data.length - 1; i++) {
            if (data[i] > data[i - 1] && data[i] > data[i + 1]) {
                if (data[i] > threshold) {
                    peaks++;
                }
            }
        }
        
        return peaks;
    }

    /**
     * Calculate Bump Score
     */
    calculateBumpScore(stats) {
        let score = 0;
        let factors = 0;

        // Factor 1: Vertical acceleration (most important)
        if (stats.zMax > this.THRESHOLDS.accelerationZ) {
            score += 0.4 * Math.min(stats.zMax / 2, 1);
            factors++;
        }

        // Factor 2: Peak count (indicates oscillation)
        if (stats.peakCount >= 2) {
            score += 0.2 * Math.min(stats.peakCount / 5, 1);
            factors++;
        }

        // Factor 3: Energy content
        if (stats.energyContent > 5) {
            score += 0.2 * Math.min(stats.energyContent / 20, 1);
            factors++;
        }

        // Factor 4: Duration (bump should last 100-1000ms)
        if (stats.duration >= this.BUMP_DETECTION.minDuration && 
            stats.duration <= this.BUMP_DETECTION.maxDuration) {
            score += 0.1 * (1 - Math.abs(stats.duration - 500) / 500);
            factors++;
        }

        // Factor 5: Speed drop detection
        const speedDrop = this.detectSpeedDrop();
        if (speedDrop > this.THRESHOLDS.speedDrop) {
            score += 0.1 * Math.min(speedDrop / 20, 1);
            factors++;
        }

        return factors > 0 ? score / factors : 0;
    }

    /**
     * Detect Speed Drop
     */
    detectSpeedDrop() {
        if (this.speedData.length < 2) return 0;

        const recentSpeeds = this.speedData.slice(-5);
        if (recentSpeeds.length < 2) return 0;

        const currentSpeed = recentSpeeds[recentSpeeds.length - 1].speed;
        const previousSpeed = recentSpeeds[0].speed;

        return Math.max(0, previousSpeed - currentSpeed);
    }

    /**
     * Detect Bump
     */
    detectBump(stats, confidence) {
        const now = Date.now();
        this.lastPredictionTime = now;

        // Get current location
        const location = this.speedData.length > 0 
            ? this.speedData[this.speedData.length - 1] 
            : null;

        const prediction = {
            id: Date.now(),
            timestamp: now,
            confidence: Math.min(confidence * 100, 100),
            location: location,
            stats: stats,
            status: 'pending' // pending, confirmed, rejected
        };

        this.predictions.push(prediction);

        // Store in localStorage
        this.savePrediction(prediction);

        // Send to server
        this.sendPredictionToServer(prediction);

        // Show notification
        this.showPredictionNotification(prediction);

        console.log('Bump detected with confidence:', prediction.confidence.toFixed(2) + '%', prediction);
    }

    /**
     * Save Prediction Locally
     */
    savePrediction(prediction) {
        try {
            const predictions = JSON.parse(localStorage.getItem('bump_predictions') || '[]');
            predictions.push(prediction);
            
            // Keep only recent predictions
            if (predictions.length > 50) {
                predictions.shift();
            }
            
            localStorage.setItem('bump_predictions', JSON.stringify(predictions));
        } catch (error) {
            console.error('Error saving prediction:', error);
        }
    }

    /**
     * Send Prediction to Server
     */
    sendPredictionToServer(prediction) {
        if (!prediction.location) return;

        fetch('/api/predictions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                latitude: prediction.location.latitude,
                longitude: prediction.location.longitude,
                confidence: prediction.confidence,
                vibration_count: prediction.stats.peakCount,
                speed_drop_count: this.detectSpeedDrop() > this.THRESHOLDS.speedDrop ? 1 : 0,
                acceleration_magnitude: prediction.stats.magnitudeMax
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                prediction.status = 'confirmed';
                console.log('Prediction sent to server:', data);
            }
        })
        .catch(error => console.error('Error sending prediction:', error));
    }

    /**
     * Show Prediction Notification
     */
    showPredictionNotification(prediction) {
        const confidence = prediction.confidence;
        let message = '';
        let type = 'info';

        if (confidence >= 80) {
            message = `🎯 تم اكتشاف مطب محتمل (${confidence.toFixed(0)}% ثقة)`;
            type = 'warning';
        } else if (confidence >= 60) {
            message = `📍 احتمال وجود مطب (${confidence.toFixed(0)}% ثقة)`;
            type = 'info';
        } else {
            message = `⚡ اهتزاز مكتشف (${confidence.toFixed(0)}% ثقة)`;
            type = 'info';
        }

        if (window.notificationManager) {
            window.notificationManager.show(message, type, 3000);
        }
    }

    /**
     * Start Monitoring
     */
    startMonitoring() {
        this.isMonitoring = true;
        console.log('Bump prediction monitoring started');
    }

    /**
     * Stop Monitoring
     */
    stopMonitoring() {
        this.isMonitoring = false;
        console.log('Bump prediction monitoring stopped');
    }

    /**
     * Get Predictions
     */
    getPredictions() {
        return this.predictions;
    }

    /**
     * Get Recent Predictions
     */
    getRecentPredictions(minutes = 30) {
        const cutoffTime = Date.now() - (minutes * 60 * 1000);
        return this.predictions.filter(p => p.timestamp > cutoffTime);
    }

    /**
     * Clear Predictions
     */
    clearPredictions() {
        this.predictions = [];
        localStorage.removeItem('bump_predictions');
    }

    /**
     * Get Statistics
     */
    getStatistics() {
        const total = this.predictions.length;
        const confirmed = this.predictions.filter(p => p.status === 'confirmed').length;
        const rejected = this.predictions.filter(p => p.status === 'rejected').length;
        const pending = this.predictions.filter(p => p.status === 'pending').length;
        
        const avgConfidence = total > 0 
            ? this.predictions.reduce((sum, p) => sum + p.confidence, 0) / total 
            : 0;

        return {
            total,
            confirmed,
            rejected,
            pending,
            averageConfidence: avgConfidence.toFixed(2),
            successRate: total > 0 ? ((confirmed / total) * 100).toFixed(2) : 0
        };
    }

    /**
     * Test Prediction
     */
    testPrediction() {
        // Simulate bump detection
        const mockStats = {
            magnitudeMean: 1.2,
            magnitudeStd: 0.5,
            magnitudeMax: 2.5,
            magnitudeMin: 0.8,
            zMean: 1.5,
            zMax: 2.8,
            xMax: 0.6,
            yMax: 0.7,
            duration: 300,
            peakCount: 3,
            energyContent: 15
        };

        this.detectBump(mockStats, 0.85);
    }

    /**
     * Get Accelerometer Data
     */
    getAccelerometerData() {
        return this.accelerometerData;
    }

    /**
     * Get Speed Data
     */
    getSpeedData() {
        return this.speedData;
    }
}

// Create global instance
window.bumpPredictor = new SmartBumpPredictor();

/**
 * Prediction Analytics
 * تحليلات التنبؤ
 */
class PredictionAnalytics {
    constructor() {
        this.events = [];
        this.maxEvents = 1000;
    }

    /**
     * Log Event
     */
    logEvent(type, data) {
        const event = {
            type,
            data,
            timestamp: Date.now()
        };

        this.events.push(event);

        if (this.events.length > this.maxEvents) {
            this.events.shift();
        }
    }

    /**
     * Get Event Statistics
     */
    getEventStatistics(timeWindow = 3600000) { // 1 hour default
        const cutoffTime = Date.now() - timeWindow;
        const recentEvents = this.events.filter(e => e.timestamp > cutoffTime);

        const stats = {
            totalEvents: recentEvents.length,
            eventTypes: {},
            averageConfidence: 0,
            peakHours: {}
        };

        recentEvents.forEach(event => {
            // Count event types
            stats.eventTypes[event.type] = (stats.eventTypes[event.type] || 0) + 1;

            // Calculate average confidence
            if (event.data.confidence) {
                stats.averageConfidence += event.data.confidence;
            }

            // Track peak hours
            const hour = new Date(event.timestamp).getHours();
            stats.peakHours[hour] = (stats.peakHours[hour] || 0) + 1;
        });

        if (recentEvents.length > 0) {
            stats.averageConfidence /= recentEvents.length;
        }

        return stats;
    }

    /**
     * Export Data
     */
    exportData() {
        return {
            events: this.events,
            exportTime: new Date().toISOString(),
            statistics: this.getEventStatistics()
        };
    }

    /**
     * Clear Data
     */
    clearData() {
        this.events = [];
    }
}

window.predictionAnalytics = new PredictionAnalytics();

/**
 * Initialize on DOM Ready
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('Smart Bump Prediction System initialized');
    
    // Auto-start monitoring if enabled in settings
    const monitoringEnabled = localStorage.getItem('settings.motion_tracking_enabled') !== '0';
    if (monitoringEnabled) {
        window.bumpPredictor.startMonitoring();
    }
});
