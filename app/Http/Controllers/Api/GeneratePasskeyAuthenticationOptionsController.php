<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\GeneratePasskeyAuthenticationOptionsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Webauthn\Exception\InvalidDataException;

class GeneratePasskeyAuthenticationOptionsController extends Controller
{
    public function __invoke(GeneratePasskeyAuthenticationOptionsAction $generateOptions): JsonResponse|string
    {
        try {
            $optionsJson = $generateOptions->handle();
        } catch (InvalidDataException|SerializerExceptionInterface $e) {
            Log::error('Webauthn 認證選項序列化失敗', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => '發生錯誤，無法序列化認證選項。',
            ], 400);
        }

        Session::flash('passkey-authentication-options', $optionsJson);

        return $optionsJson;
    }
}
