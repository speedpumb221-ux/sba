/**
 * Map 3D Effects and Advanced Interactions
 * تأثيرات الخريطة ثلاثية الأبعاد والتفاعلات المتقدمة
 */

class Map3DManager {
    constructor() {
        this.map = null;
        this.tiltAngle = 0;
        this.maxTilt = 45;
        this.isAnimating = false;
        this.particleSystem = null;
    }

    /**
     * Initialize 3D Effects
     */
    initializeEffects(mapInstance) {
        this.map = mapInstance;
        this.setupTiltEffect();
        this.setupParticleSystem();
        this.setupGlassmorphism();
        this.setupAdvancedAnimations();
    }

    /**
     * Setup Tilt Effect
     * إضافة تأثير الميلان للخريطة
     */
    setupTiltEffect() {
        const mapContainer = document.getElementById('map');
        
        mapContainer.addEventListener('mousemove', (e) => {
            if (window.innerWidth > 1024) {
                const rect = mapContainer.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const xPercent = (x / rect.width) * 100;
                const yPercent = (y / rect.height) * 100;
                
                const rotateX = ((yPercent - 50) / 50) * 5;
                const rotateY = ((xPercent - 50) / 50) * 5;
                
                mapContainer.style.transform = `
                    perspective(1200px)
                    rotateX(${rotateX}deg)
                    rotateY(${rotateY}deg)
                    scale(0.98)
                `;
            }
        });

        mapContainer.addEventListener('mouseleave', () => {
            mapContainer.style.transform = 'perspective(1200px) rotateX(0) rotateY(0) scale(1)';
        });
    }

    /**
     * Setup Particle System
     * نظام الجزيئات للتأثيرات البصرية
     */
    setupParticleSystem() {
        const canvas = document.createElement('canvas');
        canvas.id = 'particle-canvas';
        canvas.style.position = 'absolute';
        canvas.style.top = '0';
        canvas.style.left = '0';
        canvas.style.pointerEvents = 'none';
        canvas.style.zIndex = '5';
        
        const mapContainer = document.getElementById('map');
        mapContainer.appendChild(canvas);

        const ctx = canvas.getContext('2d');
        canvas.width = mapContainer.offsetWidth;
        canvas.height = mapContainer.offsetHeight;

        const particles = [];

        // Create particles on click
        document.addEventListener('click', (e) => {
            if (e.target.closest('.leaflet-marker-icon')) {
                for (let i = 0; i < 8; i++) {
                    particles.push({
                        x: e.clientX,
                        y: e.clientY,
                        vx: (Math.random() - 0.5) * 8,
                        vy: (Math.random() - 0.5) * 8 - 2,
                        life: 1,
                        size: Math.random() * 4 + 2,
                        color: `hsl(${Math.random() * 60 + 200}, 100%, 50%)`
                    });
                }
            }
        });

        // Animation loop
        const animate = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            for (let i = particles.length - 1; i >= 0; i--) {
                const p = particles[i];
                
                p.x += p.vx;
                p.y += p.vy;
                p.vy += 0.2; // gravity
                p.life -= 0.02;

                if (p.life <= 0) {
                    particles.splice(i, 1);
                    continue;
                }

                ctx.fillStyle = p.color;
                ctx.globalAlpha = p.life;
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                ctx.fill();
            }

            ctx.globalAlpha = 1;
            requestAnimationFrame(animate);
        };

        animate();
    }

    /**
     * Setup Glassmorphism Effects
     * تأثير الزجاج المتجمد على العناصر العائمة
     */
    setupGlassmorphism() {
        const controlCards = document.querySelectorAll('.control-card');
        
        controlCards.forEach(card => {
            // Add blur background
            const backdrop = document.createElement('div');
            backdrop.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(20px);
                border-radius: inherit;
                z-index: -1;
                pointer-events: none;
            `;
            card.style.position = 'relative';
            card.insertBefore(backdrop, card.firstChild);

            // Add hover effect
            card.addEventListener('mouseenter', () => {
                card.style.boxShadow = `
                    0 20px 48px rgba(0, 0, 0, 0.2),
                    0 12px 28px rgba(0, 0, 0, 0.15),
                    inset 0 1px 0 rgba(255, 255, 255, 0.3)
                `;
            });

            card.addEventListener('mouseleave', () => {
                card.style.boxShadow = `
                    0 16px 40px rgba(0, 0, 0, 0.15),
                    0 8px 20px rgba(0, 0, 0, 0.1)
                `;
            });
        });
    }

    /**
     * Setup Advanced Animations
     * تحريكات متقدمة للعناصر
     */
    setupAdvancedAnimations() {
        // Floating animation for control cards
        const style = document.createElement('style');
        style.textContent = `
            @keyframes float-advanced {
                0%, 100% {
                    transform: translateY(0px) translateX(0px);
                }
                25% {
                    transform: translateY(-8px) translateX(2px);
                }
                50% {
                    transform: translateY(-12px) translateX(-2px);
                }
                75% {
                    transform: translateY(-8px) translateX(2px);
                }
            }

            @keyframes glow-pulse {
                0%, 100% {
                    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
                }
                50% {
                    filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.2));
                }
            }

            @keyframes shimmer {
                0% {
                    background-position: -1000px 0;
                }
                100% {
                    background-position: 1000px 0;
                }
            }

            .control-card {
                animation: float-advanced 6s ease-in-out infinite;
            }

            .bump-marker {
                animation: glow-pulse 2s ease-in-out infinite;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Add Marker Ripple Effect
     * إضافة تأثير الموجة عند النقر على الماركر
     */
    addMarkerRipple(marker) {
        const originalOnClick = marker.openPopup;
        
        marker.on('click', () => {
            const latlng = marker.getLatLng();
            const point = this.map.latLngToContainerPoint(latlng);
            
            const ripple = document.createElement('div');
            ripple.style.cssText = `
                position: fixed;
                width: 40px;
                height: 40px;
                border: 2px solid rgba(59, 130, 246, 0.8);
                border-radius: 50%;
                left: ${point.x - 20}px;
                top: ${point.y - 20}px;
                pointer-events: none;
                animation: ripple-animation 0.6s ease-out;
                z-index: 999;
            `;
            
            document.body.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    }

    /**
     * Create Depth Effect for Markers
     * إنشاء تأثير العمق للماركرات
     */
    createDepthEffect(marker, depth = 1) {
        const element = marker.getElement();
        if (element) {
            element.style.filter = `drop-shadow(0 ${4 * depth}px ${8 * depth}px rgba(0, 0, 0, ${0.2 * depth}))`;
            element.style.transform = `scale(${1 + depth * 0.1})`;
        }
    }

    /**
     * Add Hover Effects to Popups
     */
    enhancePopupInteraction(popup) {
        const content = popup.getContent();
        if (content) {
            const wrapper = popup.getElement();
            if (wrapper) {
                wrapper.addEventListener('mouseenter', () => {
                    wrapper.style.transform = 'scale(1.05)';
                });
                wrapper.addEventListener('mouseleave', () => {
                    wrapper.style.transform = 'scale(1)';
                });
            }
        }
    }

    /**
     * Add Smooth Transitions
     */
    addSmoothTransitions() {
        const style = document.createElement('style');
        style.textContent = `
            .leaflet-marker-icon,
            .leaflet-popup,
            .leaflet-control {
                transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            }

            .leaflet-popup-content-wrapper {
                transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Create Ripple Animation Style
     */
    createRippleStyle() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple-animation {
                0% {
                    width: 40px;
                    height: 40px;
                    opacity: 1;
                    border-width: 2px;
                }
                100% {
                    width: 120px;
                    height: 120px;
                    opacity: 0;
                    border-width: 1px;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Initialize on DOM Ready
 */
document.addEventListener('DOMContentLoaded', () => {
    const map3D = new Map3DManager();
    
    // Wait for map to be initialized
    const checkMap = setInterval(() => {
        if (window.map) {
            map3D.initializeEffects(window.map);
            map3D.addSmoothTransitions();
            map3D.createRippleStyle();
            clearInterval(checkMap);
        }
    }, 100);
});

/**
 * Advanced Popup Manager
 * مدير النوافذ المنبثقة المتقدم
 */
class AdvancedPopupManager {
    constructor() {
        this.popups = [];
        this.maxPopups = 3;
    }

    /**
     * Create Enhanced Popup
     */
    createEnhancedPopup(content, options = {}) {
        const popup = document.createElement('div');
        popup.className = 'advanced-popup';
        popup.innerHTML = content;
        
        // Apply styles
        popup.style.cssText = `
            position: fixed;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            max-width: 400px;
            animation: popupSlideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 1px solid rgba(255, 255, 255, 0.5);
        `;

        // Position popup
        if (options.position) {
            popup.style.top = options.position.top + 'px';
            popup.style.left = options.position.left + 'px';
        }

        document.body.appendChild(popup);
        this.popups.push(popup);

        // Auto close after duration
        if (options.duration) {
            setTimeout(() => this.closePopup(popup), options.duration);
        }

        return popup;
    }

    /**
     * Close Popup
     */
    closePopup(popup) {
        popup.style.animation = 'popupSlideOut 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
        setTimeout(() => {
            popup.remove();
            this.popups = this.popups.filter(p => p !== popup);
        }, 300);
    }

    /**
     * Close All Popups
     */
    closeAllPopups() {
        this.popups.forEach(popup => this.closePopup(popup));
    }
}

// Create global instance
window.advancedPopupManager = new AdvancedPopupManager();

// Add popup animations
const style = document.createElement('style');
style.textContent = `
    @keyframes popupSlideIn {
        from {
            opacity: 0;
            transform: translateY(20px) scale(0.9);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes popupSlideOut {
        from {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        to {
            opacity: 0;
            transform: translateY(20px) scale(0.9);
        }
    }
`;
document.head.appendChild(style);
