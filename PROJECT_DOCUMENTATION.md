# تطبيق حواجز السرعة - التوثيق الشامل

## نظرة عامة

تطبيق ويب متقدم لإدارة وتتبع حواجز السرعة على الطرق، مع نظام تنبيهات ذكية وتنبؤات تلقائية. التطبيق مبني باستخدام Laravel للخادم و Vanilla JavaScript للواجهة الأمامية، مع دعم كامل للتطبيقات الويب التقدمية (PWA).

## المتطلبات الوظيفية

### 1. نظام المستخدم
- **التسجيل**: إنشاء حساب جديد مع التحقق من البيانات
- **تسجيل الدخول**: دخول آمن مع تذكر المستخدم
- **الملف الشخصي**: عرض وتعديل بيانات المستخدم
- **الإعدادات الشخصية**:
  - اختيار اللغة (عربي/إنجليزي)
  - اختيار المظهر (فاتح/غامق)
  - تحديد مسافة التنبيه (50/100/200 متر)
  - تفعيل/تعطيل الإشعارات والصوت
  - تفعيل/تعطيل GPS وتتبع الحركة

### 2. إدارة حواجز السرعة
- **عرض المطبات**: قائمة شاملة لجميع المطبات مع التفاصيل
- **إضافة مطب**: إضافة مطب جديد من الخريطة أو القائمة
- **تعديل مطب**: تحديث معلومات المطب
- **حذف مطب**: إزالة المطبات غير الصحيحة
- **معلومات المطب**:
  - الموقع الجغرافي (latitude, longitude)
  - المصدر (OSM, Google, User, Predicted)
  - مستوى الثقة (0-100%)
  - عدد التقارير
  - الحالة (مؤكد/غير مؤكد)
  - الوصف

### 3. الخريطة التفاعلية
- **عرض الخريطة**: Google Maps مع جميع المطبات
- **تتبع الموقع**: عرض موقع المستخدم الحالي
- **إضافة مطب**: الضغط على الخريطة لإضافة مطب جديد
- **عرض التفاصيل**: معلومات المطب عند الضغط عليه
- **البحث**: البحث عن مطبات محددة

### 4. نظام التنبيهات الذكية
- **تتبع GPS**: تتبع مستمر لموقع المستخدم
- **حساب المسافة**: استخدام Haversine formula
- **التنبيهات**:
  - صوت تنبيه عند الاقتراب
  - إشعار على الشاشة
  - عرض نوع المطب (مؤكد/محتمل/جديد)
- **الإعدادات**:
  - تحديد مسافة التنبيه
  - تفعيل/تعطيل الصوت
  - تفعيل/تعطيل الإشعارات

### 5. نظام التنبؤ الذكي
- **جمع البيانات**:
  - بيانات الاهتزاز (Device Motion API)
  - بيانات السرعة (Geolocation API)
  - الموقع الجغرافي
  - عدد المستخدمين

- **حساب الدرجات**:
  - اهتزاز قوي (> 15): +5 نقاط
  - انخفاض سرعة (> 10 km/h): +3 نقاط
  - تكرار من نفس المستخدم: +2 نقاط
  - تكرار من مستخدمين مختلفين: +10 نقاط

- **التصنيف**:
  - score ≥ 15: مطب مؤكد
  - score 8-15: مطب محتمل
  - score < 8: مطب محتمل

- **التحويل التلقائي**:
  - تحويل التنبؤات إلى مطبات عند تجاوز الحد الأدنى
  - حفظ المطب الجديد في قاعدة البيانات

### 6. نظام التقارير
- **أنواع التقارير**:
  - تأكيد المطب
  - تحديث المعلومات
  - إبلاغ عن خطأ
  - إضافة مطب جديد

- **تأثير التقارير**:
  - زيادة مستوى الثقة عند التأكيد
  - تقليل مستوى الثقة عند الإبلاغ عن خطأ
  - حذف المطب عند انخفاض الثقة جداً

### 7. لوحة الإدارة
- **إحصائيات**:
  - عدد المستخدمين
  - عدد المطبات
  - عدد المطبات المؤكدة
  - عدد المطبات المعلقة
  - عدد التقارير
  - عدد التنبؤات المعلقة

- **إدارة المستخدمين**: عرض وإدارة جميع المستخدمين
- **إدارة المطبات**: الموافقة أو رفض المطبات الجديدة
- **إدارة التقارير**: عرض جميع التقارير المرسلة
- **إدارة التنبؤات**: تحويل التنبؤات إلى مطبات

### 8. المظهر الليلي
- **الوضع الفاتح**: واجهة بألوان فاتحة
- **الوضع الغامق**: واجهة بألوان غامقة
- **الحفظ**: حفظ الاختيار في localStorage
- **CSS Variables**: استخدام متغيرات CSS للألوان

### 9. تصميم يشبه تطبيقات الجوال
- **Mobile-first**: تصميم موجه للهواتف الذكية
- **Full Screen**: واجهة ملء الشاشة
- **Bottom Navigation**: قائمة التنقل السفلية
- **Cards**: استخدام بطاقات للمحتوى
- **Modern UI**: تصميم حديث وبسيط

### 10. PWA (تطبيق ويب تقدمي)
- **Service Worker**: تخزين مؤقت وعمل بدون إنترنت
- **Web App Manifest**: تثبيت على الشاشة الرئيسية
- **Offline Mode**: العمل بدون اتصال إنترنت
- **Push Notifications**: إشعارات فورية

### 11. تعدد اللغات
- **العربية**: واجهة كاملة باللغة العربية
- **الإنجليزية**: واجهة كاملة باللغة الإنجليزية
- **التبديل السهل**: زر للتبديل بين اللغات

## البنية التقنية

### Backend (Laravel)
```
app/
├── Models/
│   ├── User.php - نموذج المستخدم
│   ├── SpeedBump.php - نموذج حاجز السرعة
│   ├── Report.php - نموذج التقرير
│   ├── Prediction.php - نموذج التنبؤ
│   ├── UserActivity.php - نموذج نشاط المستخدم
│   ├── DeviceEvent.php - نموذج حدث الجهاز
│   └── UserSetting.php - نموذج إعدادات المستخدم
│
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php - التحكم في المصادقة
│   │   ├── SpeedBumpController.php - التحكم في المطبات
│   │   ├── ProfileController.php - التحكم في الملف الشخصي
│   │   ├── AdminController.php - التحكم الإداري
│   │   └── DeviceEventController.php - التحكم في أحداث الجهاز
│   │
│   └── Middleware/
│       └── AdminMiddleware.php - التحقق من صلاحيات الإدارة
│
└── Database/
    └── Migrations/
        ├── create_speed_bumps_table.php
        ├── create_reports_table.php
        ├── create_device_events_table.php
        └── create_user_activities_table.php
```

### Frontend (Vanilla JavaScript)
```
public/
├── service-worker.js - Service Worker للـ PWA
├── manifest.json - Web App Manifest
└── js/
    ├── map.js - منطق الخريطة
    ├── gps.js - منطق GPS والتتبع
    ├── alerts.js - منطق التنبيهات
    ├── predictions.js - منطق التنبؤ الذكي
    └── pwa.js - منطق PWA
```

### Views (Blade Templates)
```
resources/views/
├── layouts/
│   └── app.blade.php - القالب الرئيسي
├── auth/
│   ├── login.blade.php - صفحة تسجيل الدخول
│   └── register.blade.php - صفحة التسجيل
├── bumps/
│   ├── index.blade.php - قائمة المطبات
│   └── map.blade.php - خريطة المطبات
├── profile/
│   └── show.blade.php - الملف الشخصي
├── admin/
│   ├── dashboard.blade.php - لوحة الإدارة
│   ├── users.blade.php - إدارة المستخدمين
│   ├── bumps.blade.php - إدارة المطبات
│   ├── reports.blade.php - إدارة التقارير
│   └── predictions.blade.php - إدارة التنبؤات
└── welcome.blade.php - الصفحة الرئيسية
```

## قاعدة البيانات

### جدول Users
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### جدول Speed Bumps
```sql
CREATE TABLE speed_bumps (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    source ENUM('OSM', 'Google', 'User', 'Predicted') DEFAULT 'User',
    confidence_level INT DEFAULT 50,
    report_count INT DEFAULT 1,
    type VARCHAR(255),
    description TEXT,
    is_verified BOOLEAN DEFAULT FALSE,
    score INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX (latitude, longitude)
);
```

### جدول Reports
```sql
CREATE TABLE reports (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    speed_bump_id BIGINT NOT NULL,
    report_type ENUM('confirm', 'update', 'false_positive', 'new') DEFAULT 'confirm',
    description TEXT,
    confidence INT DEFAULT 50,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (speed_bump_id) REFERENCES speed_bumps(id)
);
```

### جدول Device Events
```sql
CREATE TABLE device_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    speed DECIMAL(8, 2),
    acceleration_x DECIMAL(8, 4),
    acceleration_y DECIMAL(8, 4),
    acceleration_z DECIMAL(8, 4),
    vibration_magnitude DECIMAL(8, 4),
    is_processed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX (is_processed)
);
```

## API Endpoints

### Authentication
- `POST /register` - تسجيل حساب جديد
- `POST /login` - تسجيل الدخول
- `POST /logout` - تسجيل الخروج

### Speed Bumps API
- `GET /api/bumps` - الحصول على جميع المطبات
- `POST /api/bumps` - إضافة مطب جديد
- `PUT /api/bumps/{id}` - تعديل مطب
- `DELETE /api/bumps/{id}` - حذف مطب
- `POST /api/bumps/nearby` - الحصول على المطبات القريبة
- `POST /api/bumps/{id}/report` - إرسال تقرير

### Device Events API
- `POST /api/events` - إرسال حدث من الجهاز
- `POST /api/events/batch` - إرسال عدة أحداث

### Settings API
- `POST /api/settings` - تحديث الإعدادات

### User API
- `GET /api/user` - الحصول على بيانات المستخدم الحالي

## الأمان

### التشفير والحماية
- تشفير كلمات المرور باستخدام bcrypt
- CSRF tokens لجميع النماذج
- SQL Injection prevention باستخدام Eloquent ORM
- XSS protection في Blade templates

### الصلاحيات
- Middleware للتحقق من المصادقة
- Admin Middleware للتحقق من صلاحيات المسؤول
- Role-based access control

## الأداء

### التحسينات
- استخدام الفهارس في قاعدة البيانات
- Caching للبيانات الثابتة
- Service Worker للتخزين المؤقت
- Lazy loading للصور
- Minification للـ CSS و JavaScript

## الاختبار

### اختبارات الوحدة
```bash
php artisan test
```

### اختبارات التكامل
```bash
php artisan test --filter=Integration
```

## النشر

### على Heroku
```bash
git push heroku main
```

### على خادم Linux
```bash
# تثبيت المتطلبات
composer install --no-dev
npm install --production

# تشغيل الهجرات
php artisan migrate --force

# تحسين الأداء
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## استكشاف الأخطاء

### المشاكل الشائعة

1. **خطأ في الاتصال بقاعدة البيانات**
   - تحقق من بيانات الاتصال في `.env`
   - تأكد من تشغيل خادم قاعدة البيانات

2. **خطأ في Google Maps**
   - تحقق من مفتاح API
   - تأكد من تفعيل Maps API في Google Cloud Console

3. **Service Worker لا يعمل**
   - تأكد من أن التطبيق يعمل على HTTPS
   - امسح ذاكرة التخزين المؤقت للمتصفح

## المراجع

- [Laravel Documentation](https://laravel.com/docs)
- [Google Maps API](https://developers.google.com/maps)
- [Web APIs](https://developer.mozilla.org/en-US/docs/Web/API)
- [PWA Documentation](https://web.dev/progressive-web-apps/)

## الترخيص

هذا المشروع مرخص تحت MIT License.
