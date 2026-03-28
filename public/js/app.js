/**
 * تطبيق حواجز السرعة - JavaScript الرئيسي
 * Mobile App UI - Vanilla JS
 */

// ============================================
// 1. Dark Mode Management
// ============================================

class ThemeManager {
        /**
         * إعداد زر تبديل الثيم
         */
        setupThemeToggle() {
            const toggleBtn = document.getElementById('theme-toggle');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    this.toggleTheme();
                });
            }
        }
    constructor() {
        this.storageKey = 'theme';
        this.darkClass = 'dark';
        this.init();
    }

    init() {
        this.restoreTheme();
        this.setupThemeToggle();
        this.watchSystemTheme();
    }

    /**
     * استرجاع الثيم المحفوظ أو استخدام الافتراضي
     */
    restoreTheme() {
        let theme = localStorage.getItem(this.storageKey);
        
        if (!theme) {
            // استخدام تفضيل النظام
            theme = this.getSystemTheme();
        }
        
        this.setTheme(theme);
    }

    /**
     * الحصول على تفضيل النظام
     */
    getSystemTheme() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    /**
     * تعيين الثيم
     */
    setTheme(theme) {
        const html = document.documentElement;
        
        if (theme === 'dark') {
            html.setAttribute('data-theme', 'dark');
        } else {
            html.removeAttribute('data-theme');
        }
        
        localStorage.setItem(this.storageKey, theme);
        this.updateToggleButton(theme);
    }

    /**
     * تبديل الثيم
     */
    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
    }

    /**
     * تحديث زر التبديل
     */
    updateToggleButton(theme) {
        const toggleBtn = document.getElementById('theme-toggle');
        if (toggleBtn) {
            toggleBtn.textContent = theme === 'dark' ? '☀️' : '🌙';
        }
    }

    /**
     * مراقبة تغييرات تفضيل النظام
     */
    watchSystemTheme() {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem(this.storageKey)) {
                this.setTheme(e.matches ? 'dark' : 'light');
            }
        });
    }
}

// ============================================
// 2. Bottom Navigation Management
// ============================================

class BottomNavigation {
    constructor() {
        this.init();
    }

    init() {
        this.updateActiveLink();
        this.setupClickHandlers();
    }

    /**
     * تحديث الرابط النشط بناءً على الصفحة الحالية
     */
    updateActiveLink() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.bottom-nav a');

        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            
            if (href && currentPath.includes(href.replace(/\/$/, ''))) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }

    /**
     * إعداد معالجات النقر
     */
    setupClickHandlers() {
        const navLinks = document.querySelectorAll('.bottom-nav a');
        
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                // إزالة الفئة النشطة من جميع الروابط
                navLinks.forEach(l => l.classList.remove('active'));
                // إضافة الفئة النشطة للرابط المنقور عليه
                link.classList.add('active');
            });
        });
    }
}

// ============================================
// 3. Floating Action Button (FAB)
// ============================================

class FloatingActionButton {
    constructor(selector = '.fab') {
        this.fab = document.querySelector(selector);
        if (this.fab) {
            this.init();
        }
    }

    init() {
        this.setupClickHandler();
        this.setupScrollBehavior();
    }

    /**
     * إعداد معالج النقر
     */
    setupClickHandler() {
        this.fab.addEventListener('click', (e) => {
            e.preventDefault();
            const href = this.fab.getAttribute('href');
            if (href) {
                window.location.href = href;
            }
        });
    }

    /**
     * إخفاء/إظهار FAB عند التمرير
     */
    setupScrollBehavior() {
        let lastScrollTop = 0;
        
        window.addEventListener('scroll', () => {
            const scrollTop = window.scrollY;
            
            if (scrollTop > lastScrollTop && scrollTop > 300) {
                // التمرير لأسفل - إخفاء FAB
                this.fab.style.opacity = '0.5';
                this.fab.style.pointerEvents = 'none';
            } else {
                // التمرير لأعلى - إظهار FAB
                this.fab.style.opacity = '1';
                this.fab.style.pointerEvents = 'auto';
            }
            
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        });
    }

    /**
     * إظهار FAB
     */
    show() {
        this.fab.style.display = 'flex';
    }

    /**
     * إخفاء FAB
     */
    hide() {
        this.fab.style.display = 'none';
    }
}

// ============================================
// 4. Alert Management
// ============================================

class AlertManager {
    /**
     * إنشاء تنبيه
     */
    static create(message, type = 'info', duration = 5000) {
        const alertId = 'alert-' + Date.now();
        const alertHTML = `
            <div class="alert alert-${type}" id="${alertId}">
                <span class="alert-icon">${this.getIcon(type)}</span>
                <div class="alert-content">
                    <div>${message}</div>
                </div>
                <button class="alert-close" onclick="AlertManager.close('${alertId}')">✕</button>
            </div>
        `;

        // إضافة التنبيه إلى أعلى الصفحة
        const container = document.querySelector('main') || document.body;
        const alertElement = document.createElement('div');
        alertElement.innerHTML = alertHTML;
        container.insertBefore(alertElement.firstElementChild, container.firstChild);

        // إزالة التنبيه تلقائياً بعد المدة المحددة
        if (duration > 0) {
            setTimeout(() => {
                this.close(alertId);
            }, duration);
        }

        return alertId;
    }

    /**
     * إغلاق التنبيه
     */
    static close(alertId) {
        const alert = document.getElementById(alertId);
        if (alert) {
            alert.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }
    }

    /**
     * الحصول على أيقونة التنبيه
     */
    static getIcon(type) {
        const icons = {
            'success': '✓',
            'error': '✕',
            'warning': '⚠',
            'info': 'ℹ'
        };
        return icons[type] || icons['info'];
    }
}

// ============================================
// 5. Modal Management
// ============================================

class ModalManager {
    /**
     * فتح مودال
     */
    static open(content, options = {}) {
        const {
            title = '',
            size = 'md',
            closeButton = true,
            backdrop = true
        } = options;

        const modalId = 'modal-' + Date.now();
        
        const modalHTML = `
            <div class="modal-overlay" id="${modalId}-overlay" ${!backdrop ? 'data-no-backdrop="true"' : ''}>
                <div class="modal modal-${size}">
                    ${title ? `
                        <div class="modal-header">
                            <h2>${title}</h2>
                            ${closeButton ? `<button class="btn-close" onclick="ModalManager.close('${modalId}')">✕</button>` : ''}
                        </div>
                    ` : ''}
                    <div class="modal-body">
                        ${content}
                    </div>
                </div>
            </div>
        `;

        const modalElement = document.createElement('div');
        modalElement.id = modalId;
        modalElement.innerHTML = modalHTML;
        document.body.appendChild(modalElement);

        // إغلاق عند النقر على الخلفية
        if (backdrop) {
            document.getElementById(modalId + '-overlay').addEventListener('click', (e) => {
                if (e.target.id === modalId + '-overlay') {
                    this.close(modalId);
                }
            });
        }

        return modalId;
    }

    /**
     * إغلاق مودال
     */
    static close(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.animation = 'fadeOut 0.3s ease forwards';
            setTimeout(() => {
                modal.remove();
            }, 300);
        }
    }
}

// ============================================
// 6. Form Validation
// ============================================

class FormValidator {
    constructor(formSelector) {
        this.form = document.querySelector(formSelector);
        if (this.form) {
            this.init();
        }
    }

    init() {
        this.form.addEventListener('submit', (e) => {
            if (!this.validate()) {
                e.preventDefault();
            }
        });

        // التحقق الفوري من الحقول
        this.form.querySelectorAll('input, textarea, select').forEach(field => {
            field.addEventListener('blur', () => {
                this.validateField(field);
            });
        });
    }

    /**
     * التحقق من النموذج
     */
    validate() {
        let isValid = true;
        this.form.querySelectorAll('input, textarea, select').forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        return isValid;
    }

    /**
     * التحقق من حقل واحد
     */
    validateField(field) {
        const value = field.value.trim();
        const required = field.hasAttribute('required');
        const type = field.type;
        const pattern = field.getAttribute('pattern');

        let isValid = true;

        // التحقق من الحقول المطلوبة
        if (required && !value) {
            isValid = false;
        }

        // التحقق من البريد الإلكتروني
        if (type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            isValid = emailRegex.test(value);
        }

        // التحقق من الرقم
        if (type === 'number' && value) {
            isValid = !isNaN(value);
        }

        // التحقق من النمط المخصص
        if (pattern && value) {
            const regex = new RegExp(pattern);
            isValid = regex.test(value);
        }

        this.showFieldError(field, isValid);
        return isValid;
    }

    /**
     * عرض/إخفاء خطأ الحقل
     */
    showFieldError(field, isValid) {
        const errorElement = field.nextElementSibling;
        
        if (!isValid) {
            field.classList.add('error');
            if (errorElement && errorElement.classList.contains('form-error')) {
                errorElement.style.display = 'block';
            }
        } else {
            field.classList.remove('error');
            if (errorElement && errorElement.classList.contains('form-error')) {
                errorElement.style.display = 'none';
            }
        }
    }
}

// ============================================
// 7. Tab Management
// ============================================

class TabManager {
    constructor(containerSelector) {
        // Handle both selector strings and DOM elements
        if (typeof containerSelector === 'string') {
            this.container = document.querySelector(containerSelector);
        } else if (containerSelector instanceof Element) {
            this.container = containerSelector;
        } else {
            console.error('TabManager: Invalid container selector or element');
            return;
        }

        if (this.container) {
            this.init();
        }
    }

    init() {
        const tabs = this.container.querySelectorAll('.tab-button');
        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                this.switchTab(e.target);
            });
        });
    }

    /**
     * التبديل إلى تبويب
     */
    switchTab(tabButton) {
        const tabId = tabButton.getAttribute('data-tab');
        
        // إزالة الفئة النشطة من جميع التبويبات
        this.container.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active');
        });
        
        this.container.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });

        // إضافة الفئة النشطة للتبويب المختار
        tabButton.classList.add('active');
        const tabContent = this.container.querySelector(`[data-content="${tabId}"]`);
        if (tabContent) {
            tabContent.classList.add('active');
        }
    }
}

// ============================================
// 8. Lazy Loading Images
// ============================================

class LazyLoadManager {
    constructor() {
        if ('IntersectionObserver' in window) {
            this.init();
        }
    }

    init() {
        const images = document.querySelectorAll('img[data-src]');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.getAttribute('data-src');
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => observer.observe(img));
    }
}

// ============================================
// 9. Utility Functions
// ============================================

const Utils = {
    /**
     * تنسيق التاريخ
     */
    formatDate(date, format = 'YYYY-MM-DD') {
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');

        return format
            .replace('YYYY', year)
            .replace('MM', month)
            .replace('DD', day)
            .replace('HH', hours)
            .replace('mm', minutes);
    },

    /**
     * تنسيق الأرقام
     */
    formatNumber(num) {
        return new Intl.NumberFormat('ar-SA').format(num);
    },

    /**
     * نسخ إلى الحافظة
     */
    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            AlertManager.create('تم النسخ إلى الحافظة', 'success', 2000);
        });
    },

    /**
     * التحقق من الاتصال بالإنترنت
     */
    isOnline() {
        return navigator.onLine;
    },

    /**
     * الحصول على معامل الاستجابة
     */
    getDevicePixelRatio() {
        return window.devicePixelRatio || 1;
    },

    /**
     * التحقق من الجوال
     */
    isMobile() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }
};

// ============================================
// 10. Initialization
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    // تهيئة مدير الثيم
    new ThemeManager();

    // تهيئة التنقل السفلي
    new BottomNavigation();

    // تهيئة زر الإجراء العائم
    new FloatingActionButton();

    // تهيئة تحميل الصور البطيء
    new LazyLoadManager();

    // تهيئة نماذج التحقق
    document.querySelectorAll('form[data-validate]').forEach(form => {
        new FormValidator(form);
    });

    // تهيئة التبويبات
    document.querySelectorAll('[data-tabs]').forEach(container => {
        new TabManager(container);
    });

    // إزالة فئة التحميل
    document.body.classList.remove('loading');
});

// ============================================
// 11. Service Worker Registration (PWA)
// ============================================

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js').catch((error) => {
            console.warn('Service Worker registration failed:', error);
            // التعامل مع الخطأ بصمت
        });
    });
}

// ============================================
// 12. Global Error Handler
// ============================================

window.addEventListener('error', (event) => {
    console.error('خطأ عام:', event.error);
    // يمكن إرسال الخطأ إلى خادم التسجيل
});

window.addEventListener('unhandledrejection', (event) => {
    console.error('وعد مرفوض:', event.reason);
    // يمكن إرسال الخطأ إلى خادم التسجيل
});
