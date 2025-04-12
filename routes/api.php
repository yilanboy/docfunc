<?php

use App\Http\Controllers\Api\GeneratePasskeyAuthenticationOptionsController;
use App\Http\Controllers\Api\GeneratePasskeyRegisterOptionsController;
use App\Http\Controllers\Api\ShowAllTagsController;
use App\Http\Controllers\Api\TwitterOembedController;
use App\Http\Controllers\Api\UploadImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('user', function (Request $request) {
    return $request->user();
});

Route::get('/passkeys/register-options', GeneratePasskeyRegisterOptionsController::class)
    ->name('passkeys.register-options')
    ->middleware('auth:sanctum');

Route::get('/passkeys/authentication-options', GeneratePasskeyAuthenticationOptionsController::class)
    ->name('passkeys.authentication-options');

// Upload the image to S3
Route::middleware('auth:sanctum')
    ->post('images/upload', UploadImageController::class)
    ->name('images.store');

Route::get('tags', ShowAllTagsController::class)->name('api.tags');

Route::post('oembed/twitter', TwitterOembedController::class);
