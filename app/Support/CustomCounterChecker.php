<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Webauthn\Counter\CounterChecker;
use Webauthn\Exception\CounterException;
use Webauthn\PublicKeyCredentialSource;

class CustomCounterChecker implements CounterChecker
{
    /**
     * @throws CounterException
     */
    public function check(PublicKeyCredentialSource $publicKeyCredentialSource, int $currentCounter): void
    {
        if (in_array('usb', $publicKeyCredentialSource->transports, true)) {
            return;
        }

        try {
            $currentCounter > $publicKeyCredentialSource->counter || throw CounterException::create(
                $currentCounter,
                $publicKeyCredentialSource->counter,
                'Invalid counter.'
            );
        } catch (CounterException $throwable) {
            Log::error('Error checking counter', [
                'exception' => $throwable,
            ]);

            throw $throwable;
        }
    }
}
