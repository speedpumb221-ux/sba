/**
 * iOS Smart Permission Request System
 * نظام طلب الأذونات الذكي المتوافق مع أجهزة الآيفون
 */

class iOSPermissionManager {
    constructor() {
        this.permissions = {
            location: false,
            motion: false,
            notification: false,
            audio: false
        };

        this.permissionStatus = {
            location: 'unknown',
            motion: 'unknown',
            notification: 'unknown',
            audio: 'unknown'
        };

        this.isIOS = this.detectiOS();
        this.isWebKit = this.detectWebKit();
        this.showPermissionUI = true;

        this.initializePermissions();
    }

    /**
     * Detect iOS
     */
    detectiOS() {
        return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    }

    /**
     * Detect WebKit (Safari, Chrome on iOS)
     */
    detectWebKit() {
        return /WebKit/.test(navigator.userAgent);
    }

    /**
     * Initialize Permissions
     */
    initializePermissions() {
        if (this.isIOS) {
            console.log('🍎 iOS Device Detected - Initializing iOS-specific permissions');
            this.showPermissionRequestUI();
        } else {
            this.requestPermissionsSequentially();
        }
    }

    /**
     * Show Permission Request UI
     */
    showPermissionRequestUI() {
        const overlay = document.createElement('div');
        overlay.id = 'permission-overlay';
        overlay.className = 'permission-overlay';
        overlay.innerHTML = `
            <div class="permission-modal">
                <div class="permission-header">
                    <span class="permission-icon">🔐</span>
                    <h2>تفعيل الميزات الأمنية</h2>
                </div>

                <div class="permission-description">
                    <p>لتوفير أفضل تجربة وأماناً أثناء القيادة، نحتاج إلى بعض الأذونات:</p>
                </div>

                <div class="permission-items">
                    <div class="permission-item">
                        <div class="permission-item-icon">📍</div>
                        <div class="permission-item-content">
                            <h3>الموقع الجغرافي</h3>
                            <p>لعرض موقعك على الخريطة واكتشاف المطبات القريبة</p>
                        </div>
                        <div class="permission-item-status" id="location-status">
                            <span class="status-badge">قيد الانتظار</span>
                        </div>
                    </div>

                    <div class="permission-item">
                        <div class="permission-item-icon">📱</div>
                        <div class="permission-item-content">
                            <h3>حساسات الحركة</h3>
                            <p>لاكتشاف المطبات تلقائياً أثناء القيادة</p>
                        </div>
                        <div class="permission-item-status" id="motion-status">
                            <span class="status-badge">قيد الانتظار</span>
                        </div>
                    </div>

                    <div class="permission-item">
                        <div class="permission-item-icon">🔔</div>
                        <div class="permission-item-content">
                            <h3>الإشعارات</h3>
                            <p>للتنبيهات الفورية عند اقتراب المطبات</p>
                        </div>
                        <div class="permission-item-status" id="notification-status">
                            <span class="status-badge">قيد الانتظار</span>
                        </div>
                    </div>

                    <div class="permission-item">
                        <div class="permission-item-icon">🔊</div>
                        <div class="permission-item-content">
                            <h3>الصوت</h3>
                            <p>لتشغيل التنبيهات الصوتية</p>
                        </div>
                        <div class="permission-item-status" id="audio-status">
                            <span class="status-badge">قيد الانتظار</span>
                        </div>
                    </div>
                </div>

                <div class="permission-actions">
                    <button class="permission-btn primary" id="enable-all-btn">
                        ✓ تفعيل الكل
                    </button>
                    <button class="permission-btn secondary" id="skip-btn">
                        تخطي الآن
                    </button>
                </div>

                <div class="permission-info">
                    <p>💡 يمكنك تغيير هذه الأذونات لاحقاً من إعدادات التطبيق</p>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        this.addPermissionStyles();
        this.setupPermissionListeners();
    }

    /**
     * Setup Permission Listeners
     */
    setupPermissionListeners() {
        const enableAllBtn = document.getElementById('enable-all-btn');
        const skipBtn = document.getElementById('skip-btn');

        if (enableAllBtn) {
            enableAllBtn.addEventListener('click', () => {
                this.requestAllPermissions();
            });
        }

        if (skipBtn) {
            skipBtn.addEventListener('click', () => {
                this.closePermissionUI();
            });
        }
    }

    /**
     * Request All Permissions
     */
    async requestAllPermissions() {
        const enableAllBtn = document.getElementById('enable-all-btn');
        if (enableAllBtn) {
            enableAllBtn.disabled = true;
            enableAllBtn.textContent = '⏳ جاري التفعيل...';
        }

        // Request permissions sequentially
        await this.requestLocationPermission();
        await this.requestMotionPermission();
        await this.requestNotificationPermission();
        await this.requestAudioPermission();

        // Close UI after all permissions are requested
        setTimeout(() => {
            this.closePermissionUI();
        }, 1500);
    }

    /**
     * Request Location Permission
     */
    async requestLocationPermission() {
        return new Promise((resolve) => {
            if (!navigator.geolocation) {
                this.updatePermissionStatus('location', 'denied');
                resolve();
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.permissions.location = true;
                    this.updatePermissionStatus('location', 'granted');
                    console.log('✓ Location permission granted');
                    resolve();
                },
                (error) => {
                    this.updatePermissionStatus('location', 'denied');
                    console.warn('✗ Location permission denied:', error);
                    resolve();
                },
                {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                }
            );
        });
    }

    /**
     * Request Motion Permission
     */
    async requestMotionPermission() {
        return new Promise((resolve) => {
            if (!window.DeviceMotionEvent) {
                this.updatePermissionStatus('motion', 'denied');
                resolve();
                return;
            }

            // For iOS 13+
            if (typeof DeviceMotionEvent !== 'undefined' && typeof DeviceMotionEvent.requestPermission === 'function') {
                DeviceMotionEvent.requestPermission()
                    .then(permissionState => {
                        if (permissionState === 'granted') {
                            this.permissions.motion = true;
                            this.updatePermissionStatus('motion', 'granted');
                            window.addEventListener('devicemotion', () => {});
                            console.log('✓ Motion permission granted');
                        } else {
                            this.updatePermissionStatus('motion', 'denied');
                            console.warn('✗ Motion permission denied');
                        }
                        resolve();
                    })
                    .catch(error => {
                        this.updatePermissionStatus('motion', 'denied');
                        console.warn('✗ Motion permission error:', error);
                        resolve();
                    });
            } else {
                // For older devices
                this.permissions.motion = true;
                this.updatePermissionStatus('motion', 'granted');
                console.log('✓ Motion permission (auto-granted for older devices)');
                resolve();
            }
        });
    }

    /**
     * Request Notification Permission
     */
    async requestNotificationPermission() {
        return new Promise((resolve) => {
            if (!('Notification' in window)) {
                this.updatePermissionStatus('notification', 'denied');
                resolve();
                return;
            }

            if (Notification.permission === 'granted') {
                this.permissions.notification = true;
                this.updatePermissionStatus('notification', 'granted');
                console.log('✓ Notification permission already granted');
                resolve();
                return;
            }

            if (Notification.permission !== 'denied') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        this.permissions.notification = true;
                        this.updatePermissionStatus('notification', 'granted');
                        console.log('✓ Notification permission granted');
                    } else {
                        this.updatePermissionStatus('notification', 'denied');
                        console.warn('✗ Notification permission denied');
                    }
                    resolve();
                });
            } else {
                this.updatePermissionStatus('notification', 'denied');
                resolve();
            }
        });
    }

    /**
     * Request Audio Permission
     */
    async requestAudioPermission() {
        return new Promise((resolve) => {
            // Audio context will be created on first user interaction
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                const audioContext = new AudioContext();
                
                if (audioContext.state === 'suspended') {
                    audioContext.resume().then(() => {
                        this.permissions.audio = true;
                        this.updatePermissionStatus('audio', 'granted');
                        console.log('✓ Audio context resumed');
                        resolve();
                    });
                } else {
                    this.permissions.audio = true;
                    this.updatePermissionStatus('audio', 'granted');
                    console.log('✓ Audio permission granted');
                    resolve();
                }
            } catch (error) {
                this.updatePermissionStatus('audio', 'denied');
                console.warn('✗ Audio permission error:', error);
                resolve();
            }
        });
    }

    /**
     * Update Permission Status
     */
    updatePermissionStatus(permission, status) {
        this.permissionStatus[permission] = status;

        const statusElement = document.getElementById(`${permission}-status`);
        if (statusElement) {
            const badge = statusElement.querySelector('.status-badge');
            if (badge) {
                if (status === 'granted') {
                    badge.textContent = '✓ تم التفعيل';
                    badge.className = 'status-badge granted';
                    statusElement.parentElement.classList.add('granted');
                } else if (status === 'denied') {
                    badge.textContent = '✗ تم الرفض';
                    badge.className = 'status-badge denied';
                    statusElement.parentElement.classList.add('denied');
                }
            }
        }
    }

    /**
     * Request Permissions Sequentially (Non-iOS)
     */
    async requestPermissionsSequentially() {
        await this.requestLocationPermission();
        await this.requestMotionPermission();
        await this.requestNotificationPermission();
        await this.requestAudioPermission();
    }

    /**
     * Close Permission UI
     */
    closePermissionUI() {
        const overlay = document.getElementById('permission-overlay');
        if (overlay) {
            overlay.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => overlay.remove(), 300);
        }

        // Log permissions summary
        this.logPermissionsSummary();
    }

    /**
     * Log Permissions Summary
     */
    logPermissionsSummary() {
        console.log('📊 Permissions Summary:');
        console.log('  Location:', this.permissions.location ? '✓' : '✗');
        console.log('  Motion:', this.permissions.motion ? '✓' : '✗');
        console.log('  Notification:', this.permissions.notification ? '✓' : '✗');
        console.log('  Audio:', this.permissions.audio ? '✓' : '✗');
    }

    /**
     * Add Permission Styles
     */
    addPermissionStyles() {
        if (document.getElementById('permission-styles')) return;

        const style = document.createElement('style');
        style.id = 'permission-styles';
        style.textContent = `
            .permission-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(5px);
                -webkit-backdrop-filter: blur(5px);
                z-index: 9999;
                display: flex;
                align-items: flex-end;
                animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(100px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes fadeOut {
                from {
                    opacity: 1;
                }
                to {
                    opacity: 0;
                }
            }

            .permission-modal {
                width: 100%;
                background: white;
                border-radius: 24px 24px 0 0;
                padding: 24px;
                max-height: 90vh;
                overflow-y: auto;
                animation: modalSlideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            }

            @keyframes modalSlideUp {
                from {
                    transform: translateY(100%);
                }
                to {
                    transform: translateY(0);
                }
            }

            [data-theme="dark"] .permission-modal {
                background: #1f2937;
                color: #fff;
            }

            .permission-header {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 20px;
                text-align: center;
            }

            .permission-header h2 {
                margin: 0;
                font-size: 20px;
                font-weight: 700;
            }

            .permission-icon {
                font-size: 32px;
            }

            .permission-description {
                margin-bottom: 24px;
                color: #666;
                font-size: 14px;
                line-height: 1.6;
            }

            [data-theme="dark"] .permission-description {
                color: #aaa;
            }

            .permission-items {
                display: flex;
                flex-direction: column;
                gap: 12px;
                margin-bottom: 24px;
            }

            .permission-item {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 12px;
                border-radius: 12px;
                background: #f5f5f5;
                transition: all 0.3s ease;
            }

            [data-theme="dark"] .permission-item {
                background: rgba(255, 255, 255, 0.1);
            }

            .permission-item.granted {
                background: rgba(16, 185, 129, 0.1);
                border-left: 3px solid #10b981;
            }

            .permission-item.denied {
                background: rgba(239, 68, 68, 0.1);
                border-left: 3px solid #ef4444;
            }

            .permission-item-icon {
                font-size: 24px;
                min-width: 32px;
                text-align: center;
            }

            .permission-item-content {
                flex: 1;
            }

            .permission-item-content h3 {
                margin: 0 0 4px 0;
                font-size: 14px;
                font-weight: 600;
            }

            .permission-item-content p {
                margin: 0;
                font-size: 12px;
                color: #999;
            }

            [data-theme="dark"] .permission-item-content p {
                color: #777;
            }

            .permission-item-status {
                display: flex;
                align-items: center;
            }

            .status-badge {
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600;
                background: #e5e7eb;
                color: #666;
            }

            .status-badge.granted {
                background: rgba(16, 185, 129, 0.2);
                color: #10b981;
            }

            .status-badge.denied {
                background: rgba(239, 68, 68, 0.2);
                color: #ef4444;
            }

            .permission-actions {
                display: flex;
                gap: 12px;
                margin-bottom: 16px;
            }

            .permission-btn {
                flex: 1;
                padding: 12px 16px;
                border: none;
                border-radius: 12px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .permission-btn.primary {
                background: #3b82f6;
                color: white;
            }

            .permission-btn.primary:hover:not(:disabled) {
                background: #2563eb;
                transform: translateY(-2px);
                box-shadow: 0 8px 16px rgba(59, 130, 246, 0.3);
            }

            .permission-btn.primary:disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }

            .permission-btn.secondary {
                background: #e5e7eb;
                color: #333;
            }

            .permission-btn.secondary:hover {
                background: #d1d5db;
            }

            [data-theme="dark"] .permission-btn.secondary {
                background: rgba(255, 255, 255, 0.1);
                color: #fff;
            }

            .permission-info {
                text-align: center;
                padding: 12px;
                background: rgba(59, 130, 246, 0.1);
                border-radius: 8px;
                font-size: 12px;
                color: #3b82f6;
            }

            [data-theme="dark"] .permission-info {
                background: rgba(59, 130, 246, 0.2);
            }

            @media (max-width: 640px) {
                .permission-modal {
                    padding: 16px;
                }

                .permission-header h2 {
                    font-size: 18px;
                }

                .permission-item {
                    padding: 10px;
                }
            }
        `;

        document.head.appendChild(style);
    }

    /**
     * Get Permissions Status
     */
    getPermissionsStatus() {
        return {
            permissions: this.permissions,
            status: this.permissionStatus,
            isIOS: this.isIOS,
            allGranted: Object.values(this.permissions).every(p => p === true)
        };
    }

    /**
     * Check Permission
     */
    hasPermission(permission) {
        return this.permissions[permission] || false;
    }

    /**
     * Request Permission Manually
     */
    async requestPermissionManually(permission) {
        if (permission === 'location') {
            return this.requestLocationPermission();
        } else if (permission === 'motion') {
            return this.requestMotionPermission();
        } else if (permission === 'notification') {
            return this.requestNotificationPermission();
        } else if (permission === 'audio') {
            return this.requestAudioPermission();
        }
    }
}

// Create global instance
window.permissionManager = new iOSPermissionManager();

/**
 * Initialize on DOM Ready
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('iOS Permission Manager initialized');
});
