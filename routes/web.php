<?php

use App\Http\Controllers\User\DestroyUserController;
use App\Livewire\Pages;
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
    Route::get('/{id}', Pages\Users\ShowPage::class)
        ->name('users.show')
        ->withoutMiddleware('auth');

    Route::get('/{user}/destroy', DestroyUserController::class)
        ->name('users.destroy')
        ->withoutMiddleware('auth');
});

Route::middleware('auth')->prefix('/settings/users')->group(function () {
    Route::get('/{id}/edit', Pages\Settings\Users\EditPage::class)
        ->name('settings.users.edit');
    Route::get('/{id}/destroy', Pages\Settings\Users\DestroyPage::class)
        ->name('settings.users.destroy');

    Route::get('/{id}/password/edit', Pages\Settings\Users\Password\EditPage::class)
        ->name('settings.users.password.edit');

    Route::get('/{id}/passkeys/edit', Pages\Settings\Users\Passkeys\EditPage::class)
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
Route::get('/notifications', Pages\Notifications\IndexPage::class)
    ->middleware('auth')
    ->name('notifications.index');

// 文章分類
Route::get('/categories/{id}/{name?}', Pages\Categories\ShowPage::class)
    ->name('categories.show');

// 文章標籤
Route::get('/tags/{id}', Pages\Tags\ShowPage::class)
    ->name('tags.show');

Route::get('/comments/{id}', Pages\Comments\ShowPage::class)
    ->name('comments.show');

// Web Feed
Route::feeds();
