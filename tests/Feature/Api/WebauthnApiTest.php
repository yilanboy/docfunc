<?php

use function Pest\Laravel\get;

describe('WebAuthn API tests', function () {
    test("you can't get webauthn register options, if you are a guest", function () {
        get(route('passkeys.register-options'))
            ->assertStatus(302);
    });

    test('you must log in to get webauthn options', function () {
        loginAsUser();

        get(route('passkeys.register-options'))
            ->assertStatus(200)
            ->assertJsonStructure([
                'authenticatorSelection' => [
                    'userVerification',
                    'residentKey',
                ],
                'challenge',
                'excludeCredentials' => [],
                'pubKeyCredParams' => [],
                'rp' => [
                    'id',
                    'name',
                ],
                'user' => [
                    'id',
                    'name',
                    'displayName',
                ],
            ]);
    });

    it('can get webauthn authentication options', function () {
        get(route('passkeys.authentication-options'))
            ->assertStatus(200)
            ->assertJsonStructure([
                'allowCredentials' => [],
                'challenge',
                'rpId',
            ]);
    });
});
