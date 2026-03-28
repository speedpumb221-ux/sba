/**
 * Advanced Alert System
 * نظام التنبيهات الصوتي والبصري المتقدم
 */

class AdvancedAlertSystem {
    constructor() {
        this.audioContext = null;
        this.isInitialized = false;
        this.lastAlertTime = 0;
        this.alertCooldown = 2000; // 2 ثواني بين التنبيهات
        this.soundEnabled = true;
        this.vibrationEnabled = true;
        this.visualEnabled = true;
        this.alertHistory = [];
        this.maxHistorySize = 50;
        
        // Alert levels
        this.ALERT_LEVELS = {
            LOW: { distance: 200, color: '#f59e0b', intensity: 1 },      // أصفر - بعيد
            MEDIUM: { distance: 100, color: '#ef5350', intensity: 2 },    // برتقالي - قريب
            HIGH: { distance: 50, color: '#d32f2f', intensity: 3 },       // أحمر - قريب جداً
            CRITICAL: { distance: 20, color: '#b71c1c', intensity: 4 }    // أحمر داكن - خطر
        };

        this.initializeAudioContext();
        this.setupEventListeners();
    }

    /**
     * Initialize Web Audio API
     */
    initializeAudioContext() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            this.audioContext = new AudioContext();
            this.isInitialized = true;
        } catch (error) {
            console.warn('Web Audio API not supported:', error);
            this.isInitialized = false;
        }
    }

    /**
     * Setup Event Listeners
     */
    setupEventListeners() {
        // Listen for user interaction to enable audio
        document.addEventListener('click', () => {
            if (this.audioContext && this.audioContext.state === 'suspended') {
                this.audioContext.resume();
            }
        });

        // Load settings from localStorage
        this.soundEnabled = localStorage.getItem('settings.sound_enabled') !== '0';
        this.vibrationEnabled = localStorage.getItem('settings.vibration_enabled') !== '0';
        this.visualEnabled = localStorage.getItem('settings.visual_enabled') !== '0';
    }

    /**
     * Trigger Alert
     * تفعيل التنبيه
     */
    triggerAlert(bump, distance, userSpeed = 0) {
        const now = Date.now();
        
        // Check cooldown
        if (now - this.lastAlertTime < this.alertCooldown) {
            return;
        }

        this.lastAlertTime = now;

        // Determine alert level
        const level = this.getAlertLevel(distance);

        // Add to history
        this.addToHistory({
            bump,
            distance,
            level,
            timestamp: now,
            userSpeed
        });

        // Trigger all alert types
        if (this.soundEnabled) {
            this.playAlertSound(level, distance);
        }

        if (this.vibrationEnabled) {
            this.triggerVibration(level);
        }

        if (this.visualEnabled) {
            this.showVisualAlert(bump, level, distance, userSpeed);
        }

        // Log alert
        this.logAlert(bump, distance, level);
    }

    /**
     * Get Alert Level Based on Distance
     */
    getAlertLevel(distance) {
        if (distance <= this.ALERT_LEVELS.CRITICAL.distance) {
            return 'CRITICAL';
        } else if (distance <= this.ALERT_LEVELS.HIGH.distance) {
            return 'HIGH';
        } else if (distance <= this.ALERT_LEVELS.MEDIUM.distance) {
            return 'MEDIUM';
        } else if (distance <= this.ALERT_LEVELS.LOW.distance) {
            return 'LOW';
        }
        return null;
    }

    /**
     * Play Alert Sound
     * تشغيل صوت التنبيه
     */
    playAlertSound(level, distance) {
        if (!this.isInitialized || !this.audioContext) {
            return;
        }

        try {
            const ctx = this.audioContext;
            const now = ctx.currentTime;
            const levelData = this.ALERT_LEVELS[level];

            // Create oscillator
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.connect(gain);
            gain.connect(ctx.destination);

            // Adjust frequency based on distance and level
            const baseFrequency = this.getFrequencyForLevel(level);
            const frequency = baseFrequency + (distance > 0 ? (100 / distance) : 0);

            osc.frequency.setValueAtTime(frequency, now);
            osc.type = 'sine';

            // Create envelope
            const duration = this.getDurationForLevel(level);
            gain.gain.setValueAtTime(0.3, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + duration);

            // Add vibrato effect for higher urgency
            if (level === 'CRITICAL' || level === 'HIGH') {
                const vibrato = ctx.createOscillator();
                const vibratoGain = ctx.createGain();
                vibrato.connect(vibratoGain);
                vibratoGain.connect(osc.frequency);
                
                vibrato.frequency.setValueAtTime(6, now);
                vibratoGain.gain.setValueAtTime(50, now);
                
                vibrato.start(now);
                vibrato.stop(now + duration);
            }

            osc.start(now);
            osc.stop(now + duration);

            // Play additional beeps for critical alerts
            if (level === 'CRITICAL') {
                this.playBeepSequence(ctx, 3, 200);
            }
        } catch (error) {
            console.error('Error playing alert sound:', error);
        }
    }

    /**
     * Get Frequency for Alert Level
     */
    getFrequencyForLevel(level) {
        const frequencies = {
            'LOW': 400,        // Low tone
            'MEDIUM': 600,     // Medium tone
            'HIGH': 800,       // High tone
            'CRITICAL': 1000   // Very high tone
        };
        return frequencies[level] || 400;
    }

    /**
     * Get Duration for Alert Level
     */
    getDurationForLevel(level) {
        const durations = {
            'LOW': 0.3,
            'MEDIUM': 0.4,
            'HIGH': 0.5,
            'CRITICAL': 0.6
        };
        return durations[level] || 0.3;
    }

    /**
     * Play Beep Sequence
     */
    playBeepSequence(ctx, count, interval) {
        for (let i = 0; i < count; i++) {
            setTimeout(() => {
                const now = ctx.currentTime;
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.frequency.setValueAtTime(1000, now);
                osc.type = 'sine';

                gain.gain.setValueAtTime(0.2, now);
                gain.gain.exponentialRampToValueAtTime(0.01, now + 0.1);

                osc.start(now);
                osc.stop(now + 0.1);
            }, i * interval);
        }
    }

    /**
     * Trigger Vibration
     * تفعيل الاهتزاز
     */
    triggerVibration(level) {
        if (!navigator.vibrate) {
            return;
        }

        const patterns = {
            'LOW': [100],                    // Single short vibration
            'MEDIUM': [100, 100, 100],       // Three short vibrations
            'HIGH': [200, 100, 200],         // Longer pattern
            'CRITICAL': [300, 100, 300, 100, 300] // Urgent pattern
        };

        const pattern = patterns[level] || [100];
        navigator.vibrate(pattern);
    }

    /**
     * Show Visual Alert
     * عرض التنبيه البصري
     */
    showVisualAlert(bump, level, distance, userSpeed) {
        const levelData = this.ALERT_LEVELS[level];

        // Create alert container
        const alertContainer = document.createElement('div');
        alertContainer.className = 'advanced-visual-alert';
        alertContainer.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2000;
            pointer-events: none;
        `;

        // Create alert content
        const content = document.createElement('div');
        content.style.cssText = `
            background: ${levelData.color};
            color: white;
            padding: 30px 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            font-weight: 700;
            font-size: 24px;
            animation: alertPulse 0.5s ease-out;
            border: 3px solid rgba(255, 255, 255, 0.5);
        `;

        const levelText = {
            'LOW': '⚠️ تنبيه - مطب قريب',
            'MEDIUM': '⚠️ انتبه - مطب قريب جداً',
            'HIGH': '🚨 خطر - مطب جداً قريب',
            'CRITICAL': '🚨 خطر فوري - مطب أمامك'
        };

        content.innerHTML = `
            <div style="font-size: 32px; margin-bottom: 10px;">
                ${level === 'CRITICAL' ? '🚨' : level === 'HIGH' ? '⚠️' : '⚡'}
            </div>
            <div style="font-size: 20px; margin-bottom: 8px;">
                ${levelText[level]}
            </div>
            <div style="font-size: 14px; opacity: 0.9;">
                المسافة: ${Math.round(distance)} متر | السرعة: ${Math.round(userSpeed)} كم/س
            </div>
        `;

        alertContainer.appendChild(content);
        document.body.appendChild(alertContainer);

        // Add animation styles
        this.addAlertAnimations();

        // Remove after animation
        setTimeout(() => {
            alertContainer.style.animation = 'alertFadeOut 0.5s ease-out';
            setTimeout(() => alertContainer.remove(), 500);
        }, 3000);

        // Screen flash effect for critical alerts
        if (level === 'CRITICAL' || level === 'HIGH') {
            this.flashScreen(levelData.color);
        }
    }

    /**
     * Flash Screen
     */
    flashScreen(color) {
        const flash = document.createElement('div');
        flash.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: ${color};
            opacity: 0.3;
            z-index: 1999;
            pointer-events: none;
            animation: screenFlash 0.6s ease-out;
        `;

        document.body.appendChild(flash);

        setTimeout(() => flash.remove(), 600);
    }

    /**
     * Add Alert Animations
     */
    addAlertAnimations() {
        if (!document.getElementById('alert-animations')) {
            const style = document.createElement('style');
            style.id = 'alert-animations';
            style.textContent = `
                @keyframes alertPulse {
                    0% {
                        transform: translate(-50%, -50%) scale(0.5);
                        opacity: 0;
                    }
                    50% {
                        transform: translate(-50%, -50%) scale(1.1);
                    }
                    100% {
                        transform: translate(-50%, -50%) scale(1);
                        opacity: 1;
                    }
                }

                @keyframes alertFadeOut {
                    0% {
                        opacity: 1;
                        transform: translate(-50%, -50%) scale(1);
                    }
                    100% {
                        opacity: 0;
                        transform: translate(-50%, -50%) scale(0.8);
                    }
                }

                @keyframes screenFlash {
                    0% {
                        opacity: 0.5;
                    }
                    50% {
                        opacity: 0.3;
                    }
                    100% {
                        opacity: 0;
                    }
                }

                @keyframes pulseRing {
                    0% {
                        box-shadow: 0 0 0 0 rgba(255, 0, 0, 0.7);
                    }
                    70% {
                        box-shadow: 0 0 0 30px rgba(255, 0, 0, 0);
                    }
                    100% {
                        box-shadow: 0 0 0 0 rgba(255, 0, 0, 0);
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

    /**
     * Add to History
     */
    addToHistory(alert) {
        this.alertHistory.push(alert);
        if (this.alertHistory.length > this.maxHistorySize) {
            this.alertHistory.shift();
        }
    }

    /**
     * Log Alert
     */
    logAlert(bump, distance, level) {
        console.log(`[ALERT] ${level} - Distance: ${distance}m`, bump);
    }

    /**
     * Get Alert History
     */
    getAlertHistory() {
        return this.alertHistory;
    }

    /**
     * Clear History
     */
    clearHistory() {
        this.alertHistory = [];
    }

    /**
     * Update Settings
     */
    updateSettings(settings) {
        if (settings.hasOwnProperty('soundEnabled')) {
            this.soundEnabled = settings.soundEnabled;
        }
        if (settings.hasOwnProperty('vibrationEnabled')) {
            this.vibrationEnabled = settings.vibrationEnabled;
        }
        if (settings.hasOwnProperty('visualEnabled')) {
            this.visualEnabled = settings.visualEnabled;
        }
    }

    /**
     * Test Alert
     */
    testAlert(level = 'MEDIUM') {
        this.triggerAlert(
            { id: 0, description: 'Test Bump' },
            this.ALERT_LEVELS[level].distance - 10,
            50
        );
    }
}

// Create global instance
window.alertSystem = new AdvancedAlertSystem();

/**
 * Notification Manager
 * مدير الإشعارات المتقدم
 */
class NotificationManager {
    constructor() {
        this.notifications = [];
        this.maxNotifications = 5;
    }

    /**
     * Show Notification
     */
    show(message, type = 'info', duration = 4000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            z-index: 1500;
            animation: slideInRight 0.3s ease-out;
            border-left: 4px solid;
            max-width: 400px;
        `;

        const colors = {
            'success': '#10b981',
            'error': '#ef4444',
            'warning': '#f59e0b',
            'info': '#3b82f6'
        };

        notification.style.borderLeftColor = colors[type] || colors.info;

        const icon = {
            'success': '✓',
            'error': '✕',
            'warning': '⚠',
            'info': 'ℹ'
        };

        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 20px; color: ${colors[type]};">${icon[type]}</span>
                <span style="color: #333; font-weight: 500;">${message}</span>
            </div>
        `;

        document.body.appendChild(notification);
        this.notifications.push(notification);

        // Auto remove
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => {
                notification.remove();
                this.notifications = this.notifications.filter(n => n !== notification);
            }, 300);
        }, duration);

        return notification;
    }

    /**
     * Add Notification Animations
     */
    static addAnimations() {
        if (!document.getElementById('notification-animations')) {
            const style = document.createElement('style');
            style.id = 'notification-animations';
            style.textContent = `
                @keyframes slideInRight {
                    from {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }

                @keyframes slideOutRight {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
}

NotificationManager.addAnimations();
window.notificationManager = new NotificationManager();

/**
 * Initialize on DOM Ready
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('Alert System initialized');
});
