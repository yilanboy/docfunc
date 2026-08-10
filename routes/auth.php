<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Middleware\CheckRegistrationIsValid;
use Illuminate\Support\Facades\Route;

Route::livewire('/login', 'pages::auth.login')
    ->middleware('guest')
    ->name('login')
    ->withHead(title: '登入');

Route::livewire('/verify-email', 'pages::auth.verify-email')
    ->middleware('auth')
    ->name('verification.notice')
    ->withHead(title: '驗證電子郵件');

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::livewire('/register', 'pages::auth.register')
    ->middleware(['guest', CheckRegistrationIsValid::class])
    ->name('register')
    ->withHead(title: '註冊');

Route::livewire('/forgot-password', 'pages::auth.forgot-password')
    ->middleware('guest')
    ->name('password.request')
    ->withHead(title: '忘記密碼');

Route::livewire('/reset-password/{token}', 'pages::auth.reset-password')
    ->middleware('guest')
    ->name('password.reset')
    ->withHead(title: '重設密碼');
