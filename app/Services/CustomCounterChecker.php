<?php

namespace App\Services;

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
        if ($currentCounter >= $publicKeyCredentialSource->counter) {
            return;
        }

        throw CounterException::create(
            $currentCounter,
            $publicKeyCredentialSource->counter,
            'Invalid counter.'
        );
    }
}
