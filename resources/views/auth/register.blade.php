@extends('layouts.app')

@section('title', 'إنشاء حساب')

@section('content')
<div style="max-width: 400px; margin: 60px auto;">
    <div class="card">
        <div class="card-header">إنشاء حساب جديد</div>
        
        <form action="{{ route('register') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">الاسم</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <span style="color: #ef4444; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <span style="color: #ef4444; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" required>
                @error('password')
                    <span style="color: #ef4444; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">تأكيد كلمة المرور</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 16px;">إنشاء الحساب</button>
        </form>

        <p style="text-align: center; font-size: 14px;">
            لديك حساب بالفعل؟ <a href="{{ route('login') }}" style="color: var(--primary); text-decoration: none;">تسجيل الدخول</a>
        </p>
    </div>
</div>
@endsection
