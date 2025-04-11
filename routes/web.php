<?php

use App\Http\Controllers\User\DestroyUserController;
use App\Livewire\Pages\Categories\ShowCategoryPage;
use App\Livewire\Pages\Notifications\NotificationIndexPage;
use App\Livewire\Pages\Posts\CreatePostPage;
use App\Livewire\Pages\Posts\EditPostPage;
use App\Livewire\Pages\Posts\PostIndexPage;
use App\Livewire\Pages\Posts\ShowPostPage;
use App\Livewire\Pages\Settings\Users\DestroyUserPage;
use App\Livewire\Pages\Settings\Users\EditPasskeysPage;
use App\Livewire\Pages\Settings\Users\EditPasswordPage;
use App\Livewire\Pages\Settings\Users\EditUserPage;
use App\Livewire\Pages\Tags\ShowTagPage;
use App\Livewire\Pages\Users\ShowUserPage;
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
Route::get('/', PostIndexPage::class)->name('root');

require __DIR__.'/auth.php';

// 會員相關頁面
Route::middleware('auth')->prefix('/users')->group(function () {
    Route::get('/{id}', ShowUserPage::class)
        ->name('users.show')
        ->withoutMiddleware('auth');

    Route::get('/{user}/destroy', DestroyUserController::class)
        ->name('users.destroy')
        ->withoutMiddleware('auth');
});

Route::middleware('auth')->prefix('/settings/users')->group(function () {
    Route::get('/{id}/passkeys/edit', EditPasskeysPage::class)->name('settings.users.passkeys.edit');
    Route::get('/{id}/edit', EditUserPage::class)->name('settings.users.edit');
    Route::get('/{id}/password/edit', EditPasswordPage::class)->name('settings.users.password.edit');
    Route::get('/{id}/destroy', DestroyUserPage::class)->name('settings.users.destroy');
});

// 文章列表與內容
Route::prefix('/posts')->group(function () {
    Route::get('/', PostIndexPage::class)->name('posts.index');

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/create', CreatePostPage::class)->name('posts.create');
        Route::get('/{id}/edit', EditPostPage::class)->name('posts.edit');
    });

    // {slug?} 當中的問號代表參數為選擇性
    Route::get('/{id}/{slug?}', ShowPostPage::class)->name('posts.show');
});

// 文章分類
Route::get('/categories/{id}/{name?}', ShowCategoryPage::class)->name('categories.show');

// 文章標籤
Route::get('/tags/{id}', ShowTagPage::class)->name('tags.show');

// 通知列表
Route::get('/notifications', NotificationIndexPage::class)->name('notifications.index');

// Web Feed
Route::feeds();
