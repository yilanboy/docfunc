<?php

use App\Http\Controllers\User\DestroyUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

// 首頁
Route::livewire('/', 'pages::posts.index')->name('root');

require __DIR__.'/auth.php';

// 會員相關頁面
Route::middleware('auth')->prefix('/users')->group(function () {
    Route::livewire('/{id}', 'pages::users.show')
        ->name('users.show')
        ->withoutMiddleware('auth');

    Route::get('/{user}/destroy', DestroyUserController::class)
        ->name('users.destroy')
        ->withoutMiddleware('auth');
});

Route::middleware('auth')->prefix('/settings/users')->group(function () {
    Route::livewire('/{id}/edit', 'pages::settings.users.edit')->name('settings.users.edit');
    Route::livewire('/{id}/destroy', 'pages::settings.users.destroy')->name('settings.users.destroy');

    Route::livewire('/{id}/password/edit', 'pages::settings.users.password.edit')
        ->name('settings.users.password.edit');

    Route::livewire('/{id}/passkeys/edit', 'pages::settings.users.passkeys.edit')
        ->name('settings.users.passkeys.edit');
});

// 文章列表與內容
Route::prefix('/posts')->group(function () {
    Route::livewire('/', 'pages::posts.index')->name('posts.index');

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::livewire('/create', 'pages::posts.create')->name('posts.create');
        Route::livewire('/{id}/edit', 'pages::posts.edit')->name('posts.edit');
    });

    // {slug?} 當中的問號代表參數為選擇性
    Route::livewire('/{id}/{slug?}', 'pages::posts.show')->name('posts.show');
});

// 通知列表
Route::livewire('/notifications', 'pages::notifications.index')
    ->middleware('auth')
    ->name('notifications.index');

// 文章分類
Route::livewire('/categories/{id}/{name?}', 'pages::categories.show')
    ->name('categories.show');

// 文章標籤
Route::livewire('/tags/{id}', 'pages::tags.show')
    ->name('tags.show');

Route::livewire('/comments/{id}', 'pages::comments.show')
    ->name('comments.show');

// Web Feed
Route::feeds();
