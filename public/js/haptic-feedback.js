/**
 * Advanced Haptic Feedback System
 * نظام الاهتزاز المتقدم المتوافق مع Taptic Engine في الآيفون
 */

class HapticFeedbackManager {
    constructor() {
        this.isSupported = this.checkHapticSupport();
        this.isIOS = this.detectiOS();
        this.hapticPatterns = this.initializePatterns();
        this.isVibrationEnabled = true;
        this.lastVibrationTime = 0;
        this.vibrationCooldown = 100; // ms

        console.log('🔊 Haptic Feedback System:', {
            supported: this.isSupported,
            iOS: this.isIOS,
            vibrationAPI: !!navigator.vibrate
        });
    }

    /**
     * Check Haptic Support
     */
    checkHapticSupport() {
        return !!(
            navigator.vibrate ||
            navigator.webkitVibrate ||
            navigator.mozVibrate ||
            navigator.msVibrate
        );
    }

    /**
     * Detect iOS
     */
    detectiOS() {
        return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    }

    /**
     * Initialize Haptic Patterns
     */
    initializePatterns() {
        return {
            // Light patterns
            light: [10],
            lightDouble: [10, 20, 10],
            lightTriple: [10, 20, 10, 20, 10],

            // Medium patterns
            medium: [50],
            mediumDouble: [50, 30, 50],
            mediumTriple: [50, 30, 50, 30, 50],

            // Strong patterns
            strong: [100],
            strongDouble: [100, 50, 100],
            strongTriple: [100, 50, 100, 50, 100],

            // Alert patterns
            alert: [200, 100, 200],
            alertUrgent: [300, 100, 300, 100, 300],
            alertCritical: [400, 100, 400, 100, 400, 100, 400],

            // Bump detection patterns
            bumpDetected: [50, 30, 50, 30, 100],
            bumpWarning: [100, 50, 100],
            bumpCritical: [200, 100, 200, 100, 200],

            // Speed patterns
            speedWarning: [100, 50, 100, 50, 100],
            speedAlert: [150, 75, 150],

            // Success patterns
            success: [50, 30, 50],
            confirmation: [30, 20, 30, 20, 30],

            // Error patterns
            error: [200, 100, 200],
            errorDouble: [200, 100, 200, 100, 200],

            // Navigation patterns
            navigationTap: [20],
            navigationSwipe: [30, 20, 30],

            // Custom patterns for iOS Taptic Engine
            tapticLight: [10],
            tapticMedium: [50],
            tapticHeavy: [100],
            tapticSuccess: [50, 30, 50],
            tapticWarning: [100, 50, 100],
            tapticError: [200, 100, 200]
        };
    }

    /**
     * Vibrate with Pattern
     */
    vibrate(pattern) {
        if (!this.isSupported || !this.isVibrationEnabled) {
            return false;
        }

        // Check cooldown
        const now = Date.now();
        if (now - this.lastVibrationTime < this.vibrationCooldown) {
            return false;
        }

        this.lastVibrationTime = now;

        try {
            const vibrateFunction = navigator.vibrate || 
                                   navigator.webkitVibrate || 
                                   navigator.mozVibrate || 
                                   navigator.msVibrate;

            if (vibrateFunction) {
                vibrateFunction.call(navigator, pattern);
                return true;
            }
        } catch (error) {
            console.warn('Vibration error:', error);
            return false;
        }

        return false;
    }

    /**
     * Trigger Haptic by Name
     */
    trigger(patternName) {
        const pattern = this.hapticPatterns[patternName];
        if (pattern) {
            return this.vibrate(pattern);
        }
        console.warn(`Haptic pattern not found: ${patternName}`);
        return false;
    }

    /**
     * Light Feedback
     */
    light() {
        return this.trigger('light');
    }

    /**
     * Medium Feedback
     */
    medium() {
        return this.trigger('medium');
    }

    /**
     * Strong Feedback
     */
    strong() {
        return this.trigger('strong');
    }

    /**
     * Bump Detection Feedback
     */
    bumpDetected(distance = 100) {
        if (distance < 20) {
            return this.trigger('bumpCritical');
        } else if (distance < 50) {
            return this.trigger('bumpWarning');
        } else {
            return this.trigger('bumpDetected');
        }
    }

    /**
     * Speed Alert Feedback
     */
    speedAlert(severity = 'medium') {
        if (severity === 'critical') {
            return this.trigger('speedAlert');
        } else if (severity === 'high') {
            return this.trigger('speedWarning');
        } else {
            return this.trigger('medium');
        }
    }

    /**
     * Success Feedback
     */
    success() {
        return this.trigger('success');
    }

    /**
     * Error Feedback
     */
    error() {
        return this.trigger('error');
    }

    /**
     * Warning Feedback
     */
    warning() {
        return this.trigger('alert');
    }

    /**
     * Confirmation Feedback
     */
    confirmation() {
        return this.trigger('confirmation');
    }

    /**
     * Navigation Feedback
     */
    navigationTap() {
        return this.trigger('navigationTap');
    }

    /**
     * Navigation Swipe
     */
    navigationSwipe() {
        return this.trigger('navigationSwipe');
    }

    /**
     * Custom Vibration
     */
    custom(pattern) {
        if (Array.isArray(pattern)) {
            return this.vibrate(pattern);
        }
        return false;
    }

    /**
     * Enable/Disable Vibration
     */
    setEnabled(enabled) {
        this.isVibrationEnabled = enabled;
        localStorage.setItem('haptic_feedback_enabled', enabled ? '1' : '0');
    }

    /**
     * Is Enabled
     */
    isEnabled() {
        return this.isVibrationEnabled;
    }

    /**
     * Stop Vibration
     */
    stop() {
        if (this.isSupported) {
            try {
                const vibrateFunction = navigator.vibrate || 
                                       navigator.webkitVibrate || 
                                       navigator.mozVibrate || 
                                       navigator.msVibrate;
                if (vibrateFunction) {
                    vibrateFunction.call(navigator, 0);
                }
            } catch (error) {
                console.warn('Error stopping vibration:', error);
            }
        }
    }

    /**
     * Get Pattern
     */
    getPattern(patternName) {
        return this.hapticPatterns[patternName] || null;
    }

    /**
     * List All Patterns
     */
    listPatterns() {
        return Object.keys(this.hapticPatterns);
    }

    /**
     * Test Haptic
     */
    test(patternName = 'medium') {
        console.log(`Testing haptic pattern: ${patternName}`);
        return this.trigger(patternName);
    }

    /**
     * iOS Taptic Engine Simulation
     * محاكاة محرك Taptic Engine في الآيفون
     */
    tapticLight() {
        return this.trigger('tapticLight');
    }

    tapticMedium() {
        return this.trigger('tapticMedium');
    }

    tapticHeavy() {
        return this.trigger('tapticHeavy');
    }

    tapticSuccess() {
        return this.trigger('tapticSuccess');
    }

    tapticWarning() {
        return this.trigger('tapticWarning');
    }

    tapticError() {
        return this.trigger('tapticError');
    }

    /**
     * Advanced Haptic Sequence
     */
    sequence(patterns, interval = 100) {
        if (!Array.isArray(patterns)) {
            return false;
        }

        let delay = 0;
        patterns.forEach((pattern, index) => {
            setTimeout(() => {
                this.trigger(pattern);
            }, delay);
            delay += interval;
        });

        return true;
    }

    /**
     * Adaptive Haptic based on Distance
     */
    adaptiveHaptic(distance, maxDistance = 200) {
        const intensity = Math.max(0, 1 - (distance / maxDistance));

        if (intensity > 0.8) {
            return this.trigger('bumpCritical');
        } else if (intensity > 0.6) {
            return this.trigger('bumpWarning');
        } else if (intensity > 0.4) {
            return this.trigger('medium');
        } else if (intensity > 0.2) {
            return this.trigger('light');
        }

        return false;
    }

    /**
     * Continuous Haptic Pulse
     */
    pulse(duration = 500, interval = 100) {
        const pattern = [];
        for (let i = 0; i < duration; i += interval * 2) {
            pattern.push(interval);
            pattern.push(interval);
        }
        return this.vibrate(pattern);
    }

    /**
     * Get Status
     */
    getStatus() {
        return {
            supported: this.isSupported,
            enabled: this.isVibrationEnabled,
            iOS: this.isIOS,
            lastVibrationTime: this.lastVibrationTime,
            patternsCount: Object.keys(this.hapticPatterns).length
        };
    }
}

// Create global instance
window.hapticFeedback = new HapticFeedbackManager();

/**
 * Haptic Feedback UI Integration
 */
class HapticUIIntegration {
    constructor() {
        this.setupButtonFeedback();
        this.setupScrollFeedback();
        this.setupGestureFeedback();
    }

    /**
     * Setup Button Feedback
     */
    setupButtonFeedback() {
        document.addEventListener('click', (e) => {
            const button = e.target.closest('button, [role="button"], .btn, .clickable');
            if (button) {
                window.hapticFeedback.navigationTap();
            }
        });
    }

    /**
     * Setup Scroll Feedback
     */
    setupScrollFeedback() {
        let lastScrollTime = 0;
        const scrollThrottle = 500;

        document.addEventListener('scroll', () => {
            const now = Date.now();
            if (now - lastScrollTime > scrollThrottle) {
                window.hapticFeedback.light();
                lastScrollTime = now;
            }
        });
    }

    /**
     * Setup Gesture Feedback
     */
    setupGestureFeedback() {
        let touchStartX = 0;
        let touchStartY = 0;

        document.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        });

        document.addEventListener('touchend', (e) => {
            if (e.changedTouches.length > 0) {
                const touchEndX = e.changedTouches[0].clientX;
                const touchEndY = e.changedTouches[0].clientY;

                const deltaX = Math.abs(touchEndX - touchStartX);
                const deltaY = Math.abs(touchEndY - touchStartY);

                // Detect swipe
                if (deltaX > 50 || deltaY > 50) {
                    window.hapticFeedback.navigationSwipe();
                }
            }
        });
    }
}

// Initialize UI integration
new HapticUIIntegration();

/**
 * Initialize on DOM Ready
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('Haptic Feedback Manager initialized');

    // Load settings from localStorage
    const hapticEnabled = localStorage.getItem('haptic_feedback_enabled') !== '0';
    window.hapticFeedback.setEnabled(hapticEnabled);

    // Log status
    console.log('Haptic Status:', window.hapticFeedback.getStatus());
});
