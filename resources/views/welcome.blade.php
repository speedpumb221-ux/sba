<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تطبيق حواجز السرعة - الصفحة الرئيسية</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1e40af">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .container {
            text-align: center;
            max-width: 600px;
            padding: 20px;
        }

        .logo {
            font-size: 80px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 48px;
            margin-bottom: 16px;
            font-weight: 700;
        }

        p {
            font-size: 18px;
            margin-bottom: 32px;
            opacity: 0.95;
            line-height: 1.6;
        }

        .features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin: 40px 0;
            text-align: right;
        }

        .feature {
            background: rgba(255, 255, 255, 0.1);
            padding: 16px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }

        .feature-icon {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .feature-title {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .feature-desc {
            font-size: 12px;
            opacity: 0.8;
        }

        .buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 32px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: white;
            color: #1e40af;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 600px) {
            h1 {
                font-size: 36px;
            }

            p {
                font-size: 16px;
            }

            .features {
                grid-template-columns: 1fr;
            }

            .buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🚗</div>
        <h1>حواجز السرعة</h1>
        <p>تطبيق ذكي لتتبع وإدارة حواجز السرعة على الطرق مع تنبيهات فورية وتنبؤات ذكية</p>

        <div class="features">
            <div class="feature">
                <div class="feature-icon">🗺️</div>
                <div class="feature-title">خريطة تفاعلية</div>
                <div class="feature-desc">عرض جميع المطبات على الخريطة</div>
            </div>
            <div class="feature">
                <div class="feature-icon">🔔</div>
                <div class="feature-title">تنبيهات ذكية</div>
                <div class="feature-desc">تنبيه عند الاقتراب من المطبات</div>
            </div>
            <div class="feature">
                <div class="feature-icon">📊</div>
                <div class="feature-title">إحصائيات</div>
                <div class="feature-desc">تتبع نشاطك وإحصائياتك</div>
            </div>
            <div class="feature">
                <div class="feature-icon">🧠</div>
                <div class="feature-title">تنبؤ ذكي</div>
                <div class="feature-desc">تنبؤ بمواقع مطبات جديدة</div>
            </div>
        </div>

        <div class="buttons">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary">الذهاب إلى لوحة التحكم</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">تسجيل الدخول</a>
                <a href="{{ route('register') }}" class="btn btn-secondary">إنشاء حساب جديد</a>
            @endauth
        </div>
    </div>
</body>
</html>
