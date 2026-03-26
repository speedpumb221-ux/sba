# تطبيق حواجز السرعة - دليل الإعداد

## المتطلبات
- PHP 8.1 أو أحدث
- Composer
- MySQL أو PostgreSQL
- Node.js و npm
- Google Maps API Key (اختياري)

## خطوات التثبيت

### 1. تثبيت المتطلبات
```bash
composer install
npm install
```

### 2. إعداد قاعدة البيانات
```bash
# نسخ ملف البيئة
cp .env.example .env

# توليد مفتاح التطبيق
php artisan key:generate

# تشغيل الهجرات
php artisan migrate

# إضافة بيانات تجريبية (اختياري)
php artisan db:seed
```

### 3. إعدادات Google Maps (اختياري)
أضف مفتاح Google Maps API في ملف `.env`:
```
GOOGLE_MAPS_API_KEY=your_api_key_here
```

### 4. تشغيل التطبيق
```bash
# تشغيل خادم التطوير
php artisan serve

# في نافذة أخرى، تشغيل Vite
npm run dev
```

الآن يمكنك الوصول للتطبيق على `http://localhost:8000`

## المميزات الرئيسية

### 1. نظام المستخدم
- تسجيل حساب جديد
- تسجيل الدخول والخروج
- إدارة الملف الشخصي
- الإعدادات الشخصية

### 2. إدارة حواجز السرعة
- إضافة مطبات جديدة
- تعديل المطبات
- حذف المطبات
- عرض المطبات على الخريطة

### 3. نظام التنبيهات الذكية
- تتبع موقع المستخدم بـ GPS
- تنبيهات عند الاقتراب من المطبات
- تحديد مسافة التنبيه (50/100/200 متر)
- أصوات وإشعارات

### 4. نظام التنبؤ الذكي
- كشف الاهتزازات
- تتبع انخفاض السرعة
- حساب درجة التنبؤ
- تحويل التنبؤات إلى مطبات مؤكدة

### 5. التقارير والإحصائيات
- إرسال تقارير عن المطبات
- تأكيد أو رفض المطبات
- عرض الإحصائيات الشخصية
- لوحة الإدارة

### 6. PWA (تطبيق ويب تقدمي)
- تثبيت على الشاشة الرئيسية
- العمل بدون اتصال إنترنت
- Service Worker
- Web App Manifest

### 7. تعدد اللغات
- دعم العربية والإنجليزية
- واجهة سهلة الاستخدام

## هيكل المشروع

```
speed-bumps-app/
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   ├── SpeedBump.php
│   │   ├── Report.php
│   │   ├── Prediction.php
│   │   ├── UserActivity.php
│   │   ├── DeviceEvent.php
│   │   └── UserSetting.php
│   └── Http/
│       ├── Controllers/
│       │   ├── AuthController.php
│       │   ├── SpeedBumpController.php
│       │   ├── ProfileController.php
│       │   ├── AdminController.php
│       │   └── DeviceEventController.php
│       └── Middleware/
│           └── AdminMiddleware.php
├── database/
│   └── migrations/
├── resources/
│   └── views/
│       ├── layouts/
│       ├── auth/
│       ├── bumps/
│       ├── profile/
│       └── admin/
├── routes/
│   ├── web.php
│   └── api.php
├── public/
│   ├── service-worker.js
│   └── manifest.json
└── config/
```

## API Endpoints

### Authentication
- `POST /register` - تسجيل حساب جديد
- `POST /login` - تسجيل الدخول
- `POST /logout` - تسجيل الخروج

### Speed Bumps
- `GET /api/bumps` - الحصول على جميع المطبات
- `POST /api/bumps` - إضافة مطب جديد
- `PUT /api/bumps/{id}` - تعديل مطب
- `DELETE /api/bumps/{id}` - حذف مطب
- `POST /api/bumps/nearby` - الحصول على المطبات القريبة
- `POST /api/bumps/{id}/report` - إرسال تقرير

### Device Events
- `POST /api/events` - إرسال حدث من الجهاز
- `POST /api/events/batch` - إرسال عدة أحداث

### Settings
- `POST /api/settings` - تحديث الإعدادات

## قاعدة البيانات

### جداول رئيسية
- `users` - بيانات المستخدمين
- `speed_bumps` - حواجز السرعة
- `reports` - التقارير
- `predictions` - التنبؤات
- `device_events` - أحداث الجهاز
- `user_activities` - نشاطات المستخدمين
- `user_settings` - إعدادات المستخدمين

## نظام التنبؤ الذكي

### آلية العمل
1. تجميع بيانات الاهتزاز والسرعة من الجهاز
2. حساب درجة التنبؤ:
   - اهتزاز قوي = +5 نقاط
   - انخفاض سرعة = +3 نقاط
   - تكرار من نفس المستخدم = +2 نقاط
   - تكرار من مستخدمين مختلفين = +10 نقاط

3. التصنيف:
   - score ≥ 15 → مطب مؤكد
   - score 8-15 → مطب محتمل

## الأمان

- استخدام CSRF tokens
- تشفير كلمات المرور
- التحقق من الصلاحيات
- Middleware للمسؤولين

## الأداء

- استخدام الفهارس في قاعدة البيانات
- Caching للبيانات الثابتة
- Service Worker للتخزين المؤقت
- Lazy loading للصور

## الدعم والمساعدة

للإبلاغ عن مشاكل أو اقتراح ميزات جديدة، يرجى فتح issue على GitHub.

## الترخيص

هذا المشروع مرخص تحت MIT License.
