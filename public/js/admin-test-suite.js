/**
 * Admin Test Suite - نظام الاختبار الشامل للمسؤولين
 * يسمح باختبار جميع الأنظمة والتنبيهات والسيناريوهات
 */

class AdminTestSuite {
    constructor() {
        this.isTestMode = false;
        this.testResults = [];
        this.currentScenario = null;
        this.isAdmin = this.checkAdminStatus();

        if (this.isAdmin) {
            this.initializeTestUI();
        }
    }

    /**
     * Check Admin Status
     */
    checkAdminStatus() {
        // Check if user is admin (you can modify this based on your auth system)
        return localStorage.getItem('user_role') === 'admin' || 
               document.querySelector('[data-admin="true"]') !== null ||
               window.location.pathname.includes('/admin');
    }

    /**
     * Initialize Test UI
     */
    initializeTestUI() {
        this.createTestPanel();
        this.addTestStyles();
        console.log('🧪 Admin Test Suite initialized');
    }

    /**
     * Create Test Panel
     */
    createTestPanel() {
        const panel = document.createElement('div');
        panel.id = 'admin-test-panel';
        panel.className = 'admin-test-panel';
        panel.innerHTML = `
            <div class="test-panel-header">
                <span class="test-panel-title">🧪 لوحة الاختبار</span>
                <button class="test-panel-toggle" id="test-panel-toggle">−</button>
            </div>

            <div class="test-panel-content">
                <!-- Tabs -->
                <div class="test-tabs">
                    <button class="test-tab active" data-tab="scenarios">السيناريوهات</button>
                    <button class="test-tab" data-tab="alerts">التنبيهات</button>
                    <button class="test-tab" data-tab="permissions">الأذونات</button>
                    <button class="test-tab" data-tab="haptic">الاهتزاز</button>
                    <button class="test-tab" data-tab="results">النتائج</button>
                </div>

                <!-- Scenarios Tab -->
                <div class="test-tab-content active" id="tab-scenarios">
                    <div class="test-section">
                        <h3>محاكاة السيناريوهات</h3>
                        <div class="test-buttons">
                            <button class="test-btn" onclick="window.adminTest.simulateBumpApproach()">
                                🚗 اقتراب من مطب
                            </button>
                            <button class="test-btn" onclick="window.adminTest.simulateSpeedWarning()">
                                ⚠️ تحذير السرعة
                            </button>
                            <button class="test-btn" onclick="window.adminTest.simulateBumpDetection()">
                                📍 اكتشاف مطب
                            </button>
                            <button class="test-btn" onclick="window.adminTest.simulateCriticalAlert()">
                                🚨 تنبيه حرج
                            </button>
                        </div>
                    </div>

                    <div class="test-section">
                        <h3>محاكاة متقدمة</h3>
                        <div class="test-input-group">
                            <label>المسافة (متر):</label>
                            <input type="range" id="distance-slider" min="0" max="200" value="100" class="test-slider">
                            <span id="distance-value">100</span>
                        </div>
                        <div class="test-input-group">
                            <label>السرعة (كم/س):</label>
                            <input type="range" id="speed-slider" min="0" max="120" value="60" class="test-slider">
                            <span id="speed-value">60</span>
                        </div>
                        <div class="test-input-group">
                            <label>قوة الاهتزاز (G):</label>
                            <input type="range" id="vibration-slider" min="0" max="3" step="0.1" value="1" class="test-slider">
                            <span id="vibration-value">1.0</span>
                        </div>
                        <button class="test-btn primary" onclick="window.adminTest.simulateCustomScenario()">
                            تشغيل السيناريو المخصص
                        </button>
                    </div>
                </div>

                <!-- Alerts Tab -->
                <div class="test-tab-content" id="tab-alerts">
                    <div class="test-section">
                        <h3>اختبار التنبيهات الصوتية</h3>
                        <div class="test-buttons">
                            <button class="test-btn" onclick="window.alertSystem.playAlertSound('LOW', 200)">
                                🔊 منخفض (LOW)
                            </button>
                            <button class="test-btn" onclick="window.alertSystem.playAlertSound('MEDIUM', 100)">
                                🔊 متوسط (MEDIUM)
                            </button>
                            <button class="test-btn" onclick="window.alertSystem.playAlertSound('HIGH', 50)">
                                🔊 عالي (HIGH)
                            </button>
                            <button class="test-btn" onclick="window.alertSystem.playAlertSound('CRITICAL', 20)">
                                🔊 حرج (CRITICAL)
                            </button>
                        </div>
                    </div>

                    <div class="test-section">
                        <h3>اختبار التنبيهات البصرية</h3>
                        <div class="test-buttons">
                            <button class="test-btn" onclick="window.adminTest.testVisualAlert('LOW')">
                                👁️ منخفض
                            </button>
                            <button class="test-btn" onclick="window.adminTest.testVisualAlert('MEDIUM')">
                                👁️ متوسط
                            </button>
                            <button class="test-btn" onclick="window.adminTest.testVisualAlert('HIGH')">
                                👁️ عالي
                            </button>
                            <button class="test-btn" onclick="window.adminTest.testVisualAlert('CRITICAL')">
                                👁️ حرج
                            </button>
                        </div>
                    </div>

                    <div class="test-section">
                        <h3>اختبار الإشعارات</h3>
                        <div class="test-buttons">
                            <button class="test-btn" onclick="window.adminTest.testNotification('success')">
                                ✓ نجاح
                            </button>
                            <button class="test-btn" onclick="window.adminTest.testNotification('warning')">
                                ⚠️ تحذير
                            </button>
                            <button class="test-btn" onclick="window.adminTest.testNotification('error')">
                                ✕ خطأ
                            </button>
                            <button class="test-btn" onclick="window.adminTest.testNotification('info')">
                                ℹ️ معلومة
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Permissions Tab -->
                <div class="test-tab-content" id="tab-permissions">
                    <div class="test-section">
                        <h3>حالة الأذونات</h3>
                        <div class="permissions-status" id="permissions-status">
                            <!-- Will be filled dynamically -->
                        </div>
                    </div>

                    <div class="test-section">
                        <h3>طلب الأذونات</h3>
                        <div class="test-buttons">
                            <button class="test-btn" onclick="window.adminTest.requestAllPermissions()">
                                🔐 طلب جميع الأذونات
                            </button>
                            <button class="test-btn" onclick="window.adminTest.requestLocationPermission()">
                                📍 الموقع
                            </button>
                            <button class="test-btn" onclick="window.adminTest.requestMotionPermission()">
                                📱 الحساسات
                            </button>
                            <button class="test-btn" onclick="window.adminTest.requestNotificationPermission()">
                                🔔 الإشعارات
                            </button>
                        </div>
                    </div>

                    <div class="test-section">
                        <h3>معلومات النظام</h3>
                        <div id="system-info" class="system-info">
                            <!-- Will be filled dynamically -->
                        </div>
                    </div>
                </div>

                <!-- Haptic Tab -->
                <div class="test-tab-content" id="tab-haptic">
                    <div class="test-section">
                        <h3>اختبار الاهتزاز (Haptic)</h3>
                        <div class="test-buttons">
                            <button class="test-btn" onclick="window.hapticFeedback.light()">
                                📳 خفيف
                            </button>
                            <button class="test-btn" onclick="window.hapticFeedback.medium()">
                                📳 متوسط
                            </button>
                            <button class="test-btn" onclick="window.hapticFeedback.strong()">
                                📳 قوي
                            </button>
                            <button class="test-btn" onclick="window.hapticFeedback.success()">
                                ✓ نجاح
                            </button>
                        </div>
                    </div>

                    <div class="test-section">
                        <h3>أنماط الاهتزاز المتقدمة</h3>
                        <div class="test-buttons">
                            <button class="test-btn" onclick="window.hapticFeedback.bumpDetected(50)">
                                🚗 مطب قريب
                            </button>
                            <button class="test-btn" onclick="window.hapticFeedback.speedAlert('high')">
                                ⚠️ تحذير السرعة
                            </button>
                            <button class="test-btn" onclick="window.hapticFeedback.tapticWarning()">
                                🍎 تحذير iOS
                            </button>
                            <button class="test-btn" onclick="window.hapticFeedback.tapticError()">
                                🍎 خطأ iOS
                            </button>
                        </div>
                    </div>

                    <div class="test-section">
                        <h3>أنماط مخصصة</h3>
                        <div class="test-buttons">
                            <button class="test-btn" onclick="window.hapticFeedback.sequence(['light', 'medium', 'strong'])">
                                📳 تسلسل
                            </button>
                            <button class="test-btn" onclick="window.hapticFeedback.pulse(500, 100)">
                                📳 نبضة
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Results Tab -->
                <div class="test-tab-content" id="tab-results">
                    <div class="test-section">
                        <h3>نتائج الاختبار</h3>
                        <div id="test-results" class="test-results">
                            <!-- Will be filled dynamically -->
                        </div>
                        <button class="test-btn" onclick="window.adminTest.clearResults()">
                            🗑️ مسح النتائج
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(panel);
        this.setupEventListeners();
        this.updatePermissionsStatus();
        this.updateSystemInfo();
    }

    /**
     * Setup Event Listeners
     */
    setupEventListeners() {
        // Toggle panel
        const toggleBtn = document.getElementById('test-panel-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const content = document.querySelector('.test-panel-content');
                if (content.style.display === 'none') {
                    content.style.display = 'block';
                    toggleBtn.textContent = '−';
                } else {
                    content.style.display = 'none';
                    toggleBtn.textContent = '+';
                }
            });
        }

        // Tab switching
        document.querySelectorAll('.test-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                const tabName = e.target.dataset.tab;
                this.switchTab(tabName);
            });
        });

        // Sliders
        const distanceSlider = document.getElementById('distance-slider');
        if (distanceSlider) {
            distanceSlider.addEventListener('input', (e) => {
                document.getElementById('distance-value').textContent = e.target.value;
            });
        }

        const speedSlider = document.getElementById('speed-slider');
        if (speedSlider) {
            speedSlider.addEventListener('input', (e) => {
                document.getElementById('speed-value').textContent = e.target.value;
            });
        }

        const vibrationSlider = document.getElementById('vibration-slider');
        if (vibrationSlider) {
            vibrationSlider.addEventListener('input', (e) => {
                document.getElementById('vibration-value').textContent = parseFloat(e.target.value).toFixed(1);
            });
        }
    }

    /**
     * Switch Tab
     */
    switchTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.test-tab-content').forEach(tab => {
            tab.classList.remove('active');
        });

        // Remove active from all tab buttons
        document.querySelectorAll('.test-tab').forEach(btn => {
            btn.classList.remove('active');
        });

        // Show selected tab
        const selectedTab = document.getElementById(`tab-${tabName}`);
        if (selectedTab) {
            selectedTab.classList.add('active');
        }

        // Mark button as active
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

        // Update permissions status when switching to permissions tab
        if (tabName === 'permissions') {
            this.updatePermissionsStatus();
            this.updateSystemInfo();
        }

        // Update results when switching to results tab
        if (tabName === 'results') {
            this.displayResults();
        }
    }

    /**
     * Simulate Bump Approach
     */
    simulateBumpApproach() {
        console.log('🚗 محاكاة الاقتراب من مطب');
        
        // Simulate decreasing distance
        let distance = 200;
        const interval = setInterval(() => {
            distance -= 20;

            if (window.alertSystem) {
                window.alertSystem.triggerAlert(
                    { id: 1, description: 'Test Bump' },
                    distance,
                    60
                );
            }

            if (distance <= 0) {
                clearInterval(interval);
                this.addTestResult('✓ محاكاة الاقتراب من المطب', 'نجح');
            }
        }, 500);
    }

    /**
     * Simulate Speed Warning
     */
    simulateSpeedWarning() {
        console.log('⚠️ محاكاة تحذير السرعة');
        
        if (window.speedIndicators) {
            window.speedIndicators.showSpeedAlert(95, 80);
        }

        if (window.hapticFeedback) {
            window.hapticFeedback.speedAlert('high');
        }

        this.addTestResult('⚠️ تحذير السرعة', 'نجح');
    }

    /**
     * Simulate Bump Detection
     */
    simulateBumpDetection() {
        console.log('📍 محاكاة اكتشاف مطب');
        
        if (window.bumpPredictor) {
            window.bumpPredictor.testPrediction();
        }

        this.addTestResult('📍 اكتشاف المطب', 'نجح');
    }

    /**
     * Simulate Critical Alert
     */
    simulateCriticalAlert() {
        console.log('🚨 محاكاة تنبيه حرج');
        
        if (window.alertSystem) {
            window.alertSystem.triggerAlert(
                { id: 1, description: 'Critical Bump' },
                15,
                70
            );
        }

        this.addTestResult('🚨 التنبيه الحرج', 'نجح');
    }

    /**
     * Simulate Custom Scenario
     */
    simulateCustomScenario() {
        const distance = parseFloat(document.getElementById('distance-slider').value);
        const speed = parseFloat(document.getElementById('speed-slider').value);
        const vibration = parseFloat(document.getElementById('vibration-slider').value);

        console.log(`🎯 محاكاة مخصصة: المسافة=${distance}م، السرعة=${speed}كم/س، الاهتزاز=${vibration}G`);

        if (window.alertSystem) {
            window.alertSystem.triggerAlert(
                { id: 1, description: 'Custom Test' },
                distance,
                speed
            );
        }

        if (window.hapticFeedback) {
            window.hapticFeedback.adaptiveHaptic(distance, 200);
        }

        this.addTestResult(`🎯 السيناريو المخصص (${distance}م، ${speed}كم/س)`, 'نجح');
    }

    /**
     * Test Visual Alert
     */
    testVisualAlert(level) {
        console.log(`👁️ اختبار التنبيه البصري: ${level}`);
        
        if (window.alertSystem) {
            window.alertSystem.showVisualAlert(
                { id: 1, description: 'Test' },
                level,
                100,
                60
            );
        }

        this.addTestResult(`👁️ التنبيه البصري (${level})`, 'نجح');
    }

    /**
     * Test Notification
     */
    testNotification(type) {
        console.log(`🔔 اختبار الإشعار: ${type}`);
        
        if (window.notificationManager) {
            const messages = {
                'success': '✓ تم الاختبار بنجاح',
                'warning': '⚠️ تحذير الاختبار',
                'error': '✕ خطأ في الاختبار',
                'info': 'ℹ️ معلومة الاختبار'
            };

            window.notificationManager.show(messages[type], type, 3000);
        }

        this.addTestResult(`🔔 الإشعار (${type})`, 'نجح');
    }

    /**
     * Update Permissions Status
     */
    updatePermissionsStatus() {
        const statusDiv = document.getElementById('permissions-status');
        if (!statusDiv) return;

        const permStatus = window.permissionManager ? 
            window.permissionManager.getPermissionsStatus() : 
            { permissions: {}, status: {} };

        const permissions = [
            { name: 'location', label: '📍 الموقع' },
            { name: 'motion', label: '📱 الحساسات' },
            { name: 'notification', label: '🔔 الإشعارات' },
            { name: 'audio', label: '🔊 الصوت' }
        ];

        let html = '';
        permissions.forEach(perm => {
            const granted = permStatus.permissions[perm.name] || false;
            const status = granted ? '✓ مفعل' : '✗ معطل';
            const color = granted ? 'green' : 'red';
            html += `
                <div class="permission-item" style="border-left: 3px solid ${color};">
                    <span>${perm.label}</span>
                    <span style="color: ${color}; font-weight: bold;">${status}</span>
                </div>
            `;
        });

        statusDiv.innerHTML = html;
    }

    /**
     * Update System Info
     */
    updateSystemInfo() {
        const infoDiv = document.getElementById('system-info');
        if (!infoDiv) return;

        const userAgent = navigator.userAgent;
        const isIOS = /iPad|iPhone|iPod/.test(userAgent);
        const isAndroid = /Android/.test(userAgent);
        const platform = isIOS ? '🍎 iOS' : isAndroid ? '🤖 Android' : '💻 Desktop';

        const hapticStatus = window.hapticFeedback ? 
            (window.hapticFeedback.isSupported ? '✓ مدعوم' : '✗ غير مدعوم') : 
            'غير معروف';

        const speedIndicatorStatus = window.speedIndicators ? '✓ نشط' : '✗ معطل';

        const info = `
            <div class="info-item">
                <strong>المنصة:</strong> ${platform}
                <br><strong>المتصفح:</strong> ${this.getBrowserName()}
                <br><strong>الاهتزاز:</strong> ${hapticStatus}
                <br><strong>مؤشر السرعة:</strong> ${speedIndicatorStatus}
                <br><strong>وقت النظام:</strong> ${new Date().toLocaleTimeString('ar-SA')}
            </div>
        `;

        infoDiv.innerHTML = info;
    }

    /**
     * Get Browser Name
     */
    getBrowserName() {
        const userAgent = navigator.userAgent;
        if (userAgent.indexOf('Chrome') > -1) return 'Chrome';
        if (userAgent.indexOf('Safari') > -1) return 'Safari';
        if (userAgent.indexOf('Firefox') > -1) return 'Firefox';
        if (userAgent.indexOf('Edge') > -1) return 'Edge';
        return 'Unknown';
    }

    /**
     * Request All Permissions
     */
    async requestAllPermissions() {
        if (window.permissionManager) {
            await window.permissionManager.requestAllPermissions();
            this.updatePermissionsStatus();
            this.addTestResult('🔐 طلب جميع الأذونات', 'نجح');
        }
    }

    /**
     * Request Location Permission
     */
    async requestLocationPermission() {
        if (window.permissionManager) {
            await window.permissionManager.requestLocationPermission();
            this.updatePermissionsStatus();
            this.addTestResult('📍 طلب إذن الموقع', 'نجح');
        }
    }

    /**
     * Request Motion Permission
     */
    async requestMotionPermission() {
        if (window.permissionManager) {
            await window.permissionManager.requestMotionPermission();
            this.updatePermissionsStatus();
            this.addTestResult('📱 طلب إذن الحساسات', 'نجح');
        }
    }

    /**
     * Request Notification Permission
     */
    async requestNotificationPermission() {
        if (window.permissionManager) {
            await window.permissionManager.requestNotificationPermission();
            this.updatePermissionsStatus();
            this.addTestResult('🔔 طلب إذن الإشعارات', 'نجح');
        }
    }

    /**
     * Add Test Result
     */
    addTestResult(test, result, details = '') {
        const timestamp = new Date().toLocaleTimeString('ar-SA');
        this.testResults.push({
            test,
            result,
            details,
            timestamp
        });

        console.log(`✅ ${test}: ${result}`);
    }

    /**
     * Display Results
     */
    displayResults() {
        const resultsDiv = document.getElementById('test-results');
        if (!resultsDiv) return;

        if (this.testResults.length === 0) {
            resultsDiv.innerHTML = '<p style="text-align: center; color: #999;">لا توجد نتائج اختبار حتى الآن</p>';
            return;
        }

        let html = '<table style="width: 100%; border-collapse: collapse;">';
        html += '<tr style="background: #f0f0f0;"><th>الاختبار</th><th>النتيجة</th><th>الوقت</th></tr>';

        this.testResults.forEach(result => {
            const resultColor = result.result === 'نجح' ? 'green' : 'red';
            html += `
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 8px;">${result.test}</td>
                    <td style="padding: 8px; color: ${resultColor}; font-weight: bold;">${result.result}</td>
                    <td style="padding: 8px; font-size: 12px;">${result.timestamp}</td>
                </tr>
            `;
        });

        html += '</table>';
        resultsDiv.innerHTML = html;
    }

    /**
     * Clear Results
     */
    clearResults() {
        this.testResults = [];
        this.displayResults();
        console.log('🗑️ تم مسح نتائج الاختبار');
    }

    /**
     * Add Test Styles
     */
    addTestStyles() {
        if (document.getElementById('admin-test-styles')) return;

        const style = document.createElement('style');
        style.id = 'admin-test-styles';
        style.textContent = `
            .admin-test-panel {
                position: fixed;
                bottom: 100px;
                right: 20px;
                width: 400px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
                z-index: 5000;
                font-family: Arial, sans-serif;
                max-height: 600px;
                overflow-y: auto;
            }

            [data-theme="dark"] .admin-test-panel {
                background: #1f2937;
                color: white;
            }

            .test-panel-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px 16px;
                background: #3b82f6;
                color: white;
                border-radius: 12px 12px 0 0;
                font-weight: bold;
            }

            .test-panel-toggle {
                background: none;
                border: none;
                color: white;
                font-size: 20px;
                cursor: pointer;
            }

            .test-panel-content {
                padding: 16px;
            }

            .test-tabs {
                display: flex;
                gap: 8px;
                margin-bottom: 16px;
                border-bottom: 2px solid #e5e7eb;
                overflow-x: auto;
            }

            [data-theme="dark"] .test-tabs {
                border-bottom-color: #374151;
            }

            .test-tab {
                padding: 8px 12px;
                background: none;
                border: none;
                cursor: pointer;
                font-size: 12px;
                font-weight: 600;
                color: #666;
                border-bottom: 2px solid transparent;
                margin-bottom: -2px;
            }

            .test-tab.active {
                color: #3b82f6;
                border-bottom-color: #3b82f6;
            }

            [data-theme="dark"] .test-tab {
                color: #999;
            }

            [data-theme="dark"] .test-tab.active {
                color: #60a5fa;
                border-bottom-color: #60a5fa;
            }

            .test-tab-content {
                display: none;
            }

            .test-tab-content.active {
                display: block;
            }

            .test-section {
                margin-bottom: 16px;
            }

            .test-section h3 {
                margin: 0 0 12px 0;
                font-size: 14px;
                font-weight: bold;
            }

            .test-buttons {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .test-btn {
                padding: 8px 12px;
                background: #e5e7eb;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-size: 12px;
                font-weight: 600;
                transition: all 0.3s;
            }

            .test-btn:hover {
                background: #d1d5db;
                transform: translateY(-2px);
            }

            .test-btn.primary {
                background: #3b82f6;
                color: white;
            }

            .test-btn.primary:hover {
                background: #2563eb;
            }

            [data-theme="dark"] .test-btn {
                background: #374151;
                color: white;
            }

            [data-theme="dark"] .test-btn:hover {
                background: #4b5563;
            }

            .test-input-group {
                margin-bottom: 12px;
            }

            .test-input-group label {
                display: block;
                font-size: 12px;
                margin-bottom: 4px;
                font-weight: 600;
            }

            .test-slider {
                width: 100%;
                height: 6px;
                border-radius: 3px;
                background: #e5e7eb;
                outline: none;
                -webkit-appearance: none;
                appearance: none;
            }

            .test-slider::-webkit-slider-thumb {
                -webkit-appearance: none;
                appearance: none;
                width: 18px;
                height: 18px;
                border-radius: 50%;
                background: #3b82f6;
                cursor: pointer;
            }

            .test-slider::-moz-range-thumb {
                width: 18px;
                height: 18px;
                border-radius: 50%;
                background: #3b82f6;
                cursor: pointer;
                border: none;
            }

            .permission-item {
                padding: 8px 12px;
                margin-bottom: 8px;
                background: #f5f5f5;
                border-radius: 6px;
                display: flex;
                justify-content: space-between;
                font-size: 12px;
            }

            [data-theme="dark"] .permission-item {
                background: #374151;
            }

            .system-info {
                padding: 12px;
                background: #f5f5f5;
                border-radius: 6px;
                font-size: 12px;
                line-height: 1.6;
            }

            [data-theme="dark"] .system-info {
                background: #374151;
            }

            .test-results {
                font-size: 12px;
                overflow-x: auto;
            }

            .test-results table {
                font-size: 11px;
            }

            .test-results th {
                text-align: right;
                font-weight: bold;
            }

            @media (max-width: 640px) {
                .admin-test-panel {
                    width: calc(100% - 40px);
                    right: 20px;
                    left: 20px;
                }

                .test-buttons {
                    grid-template-columns: 1fr;
                }
            }
        `;

        document.head.appendChild(style);
    }
}

// Create global instance
window.adminTest = new AdminTestSuite();

/**
 * Initialize on DOM Ready
 */
document.addEventListener('DOMContentLoaded', () => {
    if (window.adminTest.isAdmin) {
        console.log('🧪 Admin Test Suite is active');
    }
});
