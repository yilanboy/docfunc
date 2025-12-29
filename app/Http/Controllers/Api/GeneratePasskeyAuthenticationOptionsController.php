<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Serializer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use Throwable;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialRequestOptions;

class GeneratePasskeyAuthenticationOptionsController extends Controller
{
    public function __invoke(Serializer $serializer)
    {
        try {
            $options = new PublicKeyCredentialRequestOptions(
                challenge: Str::random(),
                rpId: Uri::of(config('app.url'))->host(),
                allowCredentials: [],
            );
        } catch (InvalidDataException $e) {
            Log::error('無法建立 Webauthn 認證選項', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => '無法建立金鑰認證選項，請稍後再試。',
            ], 400);
        }

        try {
            $optionsJson = $serializer->toJson($options);
        } catch (Throwable $e) {
            Log::error('Webauthn 認證選項序列化失敗', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => '伺服器發生錯誤，無法序列化認證選項。',
            ], 400);
        }

        Session::flash('passkey-authentication-options', $optionsJson);

        return $optionsJson;
    }
}
