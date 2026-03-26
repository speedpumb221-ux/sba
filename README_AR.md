# 🚗 تطبيق حواجز السرعة - Speed Bumps App

تطبيق ويب متقدم وذكي لتتبع وإدارة حواجز السرعة على الطرق، مع نظام تنبيهات فورية وتنبؤات ذكية بدون استخدام Python.

## 🎯 المميزات الرئيسية

### 1. 🗺️ خريطة تفاعلية
- عرض جميع حواجز السرعة على Google Maps
- تتبع موقع المستخدم الحالي
- إضافة مطبات جديدة بالضغط على الخريطة
- عرض تفاصيل المطب عند الضغط عليه

### 2. 🔔 نظام التنبيهات الذكية
- تتبع GPS مستمر للموقع
- تنبيهات صوتية وبصرية عند الاقتراب من المطبات
- تحديد مسافة التنبيه (50/100/200 متر)
- حساب المسافة باستخدام Haversine formula

### 3. 🧠 نظام التنبؤ الذكي
- كشف الاهتزازات باستخدام Device Motion API
- تتبع انخفاض السرعة
- نظام حساب الدرجات:
  - اهتزاز قوي: +5 نقاط
  - انخفاض سرعة: +3 نقاط
  - تكرار من نفس المستخدم: +2 نقاط
  - تكرار من مستخدمين مختلفين: +10 نقاط
- تحويل تلقائي للتنبؤات إلى مطبات مؤكدة

### 4. 👤 نظام المستخدم
- تسجيل حساب جديد وآمن
- تسجيل دخول وخروج
- ملف شخصي مع الإحصائيات
- إدارة الإعدادات الشخصية

### 5. 📊 نظام التقارير والإحصائيات
- إرسال تقارير عن المطبات
- تأكيد أو رفض المطبات
- عرض الإحصائيات الشخصية
- تتبع النشاطات

### 6. ⚙️ لوحة الإدارة
- إدارة المستخدمين
- إدارة المطبات
- إدارة التقارير
- إدارة التنبؤات
- عرض الإحصائيات الشاملة

### 7. 🌙 الوضع الليلي
- دعم Light و Dark Mode
- حفظ الاختيار في localStorage
- استخدام CSS Variables

### 8. 📱 تصميم يشبه تطبيقات الجوال
- Mobile-first design
- واجهة ملء الشاشة
- Bottom Navigation ثابت
- تصميم حديث وبسيط

### 9. 📡 PWA (تطبيق ويب تقدمي)
- Service Worker للعمل بدون إنترنت
- Web App Manifest
- تثبيت على الشاشة الرئيسية
- Push Notifications

### 10. 🌐 تعدد اللغات
- دعم العربية والإنجليزية
- واجهة سهلة الاستخدام

---

## 🛠️ المتطلبات التقنية

- **Backend**: Laravel 10 (PHP 8.1+)
- **Frontend**: HTML, CSS, Vanilla JavaScript
- **Database**: MySQL أو PostgreSQL
- **APIs**: Google Maps API (اختياري)
- **Browser APIs**: 
  - Geolocation API
  - Device Motion API
  - Service Worker API
  - Web App Manifest

---

## 📦 التثبيت والإعداد

### 1. فك ضغط الملف
```bash
tar -xzf speed-bumps-app.tar.gz
# أو
unzip speed-bumps-app.zip
cd speed-bumps-app
```

### 2. تثبيت المتطلبات
```bash
composer install
npm install
```

### 3. إعداد البيئة
```bash
cp .env.example .env
php artisan key:generate
```

### 4. إعداد قاعدة البيانات
```bash
# تحديث بيانات الاتصال في .env
php artisan migrate
```

### 5. إضافة Google Maps API (اختياري)
```bash
# أضف المفتاح في .env
GOOGLE_MAPS_API_KEY=your_api_key_here
```

### 6. تشغيل التطبيق
```bash
# في نافذة أولى
php artisan serve

# في نافذة ثانية
npm run dev
```

### 7. الوصول للتطبيق
```
http://localhost:8000
```

---

## 🚀 الاستخدام

### إنشاء حساب جديد
1. انقر على "إنشاء حساب جديد"
2. أدخل الاسم والبريد الإلكتروني وكلمة المرور
3. انقر على "إنشاء الحساب"

### تسجيل الدخول
1. أدخل البريد الإلكتروني وكلمة المرور
2. انقر على "تسجيل الدخول"

### إضافة مطب جديد
1. انتقل إلى الخريطة
2. اضغط على الموقع المطلوب
3. أضف الوصف (اختياري)
4. انقر على "إضافة"

### تفعيل التنبيهات
1. انتقل إلى الخريطة
2. اختر مسافة التنبيه (50/100/200 متر)
3. انقر على "بدء التتبع"
4. سيتم تنبيهك عند الاقتراب من مطب

### إرسال تقرير
1. انتقل إلى قائمة المطبات
2. اختر المطب
3. انقر على "تأكيد" أو "خطأ"
4. أضف تعليق (اختياري)

### الوصول لوحة الإدارة
1. تسجيل الدخول كمسؤول
2. انقر على "الإدارة" في القائمة العلوية
3. أدر المستخدمين والمطبات والتقارير

---

## 📁 هيكل المشروع

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
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php
│   ├── auth/
│   │   ├── login.blade.php
│   │   └── register.blade.php
│   ├── bumps/
│   │   ├── index.blade.php
│   │   └── map.blade.php
│   ├── profile/
│   │   └── show.blade.php
│   ├── admin/
│   │   ├── dashboard.blade.php
│   │   ├── users.blade.php
│   │   ├── bumps.blade.php
│   │   ├── reports.blade.php
│   │   └── predictions.blade.php
│   └── welcome.blade.php
├── routes/
│   ├── web.php
│   └── api.php
├── database/
│   └── migrations/
├── public/
│   ├── service-worker.js
│   └── manifest.json
├── SETUP.md
├── PROJECT_DOCUMENTATION.md
└── README_AR.md
```

---

## 🔐 الأمان

- ✅ CSRF Protection
- ✅ Password Hashing (bcrypt)
- ✅ SQL Injection Prevention
- ✅ XSS Protection
- ✅ Role-based Access Control
- ✅ Secure Headers

---

## 📊 قاعدة البيانات

### الجداول الرئيسية
1. **users** - بيانات المستخدمين
2. **speed_bumps** - حواجز السرعة
3. **reports** - التقارير
4. **predictions** - التنبؤات
5. **device_events** - أحداث الجهاز
6. **user_activities** - نشاطات المستخدمين
7. **user_settings** - إعدادات المستخدمين

---

## 🔌 API Endpoints

### المصادقة
- `POST /register` - تسجيل حساب جديد
- `POST /login` - تسجيل الدخول
- `POST /logout` - تسجيل الخروج

### حواجز السرعة
- `GET /api/bumps` - الحصول على جميع المطبات
- `POST /api/bumps` - إضافة مطب جديد
- `PUT /api/bumps/{id}` - تعديل مطب
- `DELETE /api/bumps/{id}` - حذف مطب
- `POST /api/bumps/nearby` - الحصول على المطبات القريبة
- `POST /api/bumps/{id}/report` - إرسال تقرير

### أحداث الجهاز
- `POST /api/events` - إرسال حدث من الجهاز
- `POST /api/events/batch` - إرسال عدة أحداث

### الإعدادات
- `POST /api/settings` - تحديث الإعدادات

---

## 🐛 استكشاف الأخطاء

### المشاكل الشائعة

**خطأ في الاتصال بقاعدة البيانات**
```bash
# تحقق من بيانات الاتصال في .env
# تأكد من تشغيل خادم قاعدة البيانات
php artisan migrate
```

**خطأ في Google Maps**
```bash
# تحقق من مفتاح API
# تأكد من تفعيل Maps API في Google Cloud Console
```

**Service Worker لا يعمل**
```bash
# تأكد من أن التطبيق يعمل على HTTPS
# امسح ذاكرة التخزين المؤقت للمتصفح
```

---

## 📚 التوثيق الإضافية

- **SETUP.md** - دليل التثبيت والإعداد
- **PROJECT_DOCUMENTATION.md** - التوثيق الشامل للمشروع

---

## 🤝 المساهمة

نرحب بمساهماتك! يرجى:
1. Fork المشروع
2. إنشاء فرع جديد (`git checkout -b feature/AmazingFeature`)
3. Commit التغييرات (`git commit -m 'Add some AmazingFeature'`)
4. Push إلى الفرع (`git push origin feature/AmazingFeature`)
5. فتح Pull Request

---

## 📝 الترخيص

هذا المشروع مرخص تحت MIT License. انظر ملف `LICENSE` للتفاصيل.

---

## 📧 التواصل

للأسئلة والاقتراحات:
- البريد الإلكتروني: support@speedbumps.local
- GitHub Issues: [فتح issue جديد]

---

## 🙏 شكر خاص

شكر خاص لـ:
- فريق Laravel
- فريق Google Maps
- المساهمين والمستخدمين

---

**استمتع باستخدام التطبيق! 🎉**
