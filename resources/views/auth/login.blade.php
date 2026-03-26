@extends('layouts.app')

@section('title', 'تسجيل الدخول')

@section('content')
<div style="max-width: 400px; margin: 60px auto;">
    <div class="card">
        <div class="card-header">تسجيل الدخول</div>
        
        <form action="{{ route('login') }}" method="POST">
            @csrf

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
                <label>
                    <input type="checkbox" name="remember"> تذكرني
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 16px;">تسجيل الدخول</button>
        </form>

        <p style="text-align: center; font-size: 14px;">
            ليس لديك حساب؟ <a href="{{ route('register') }}" style="color: var(--primary); text-decoration: none;">إنشاء حساب</a>
        </p>
    </div>
</div>
@endsection
