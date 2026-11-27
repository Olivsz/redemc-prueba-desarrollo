<?php

declare(strict_types=1);

namespace App\Http;

final class Session
{
    public static function start(Request $request): void
    {
        session_start([
            'name' => 'redemc_session',
            'cookie_httponly' => true,
            'cookie_samesite' => 'Strict',
            'cookie_secure' => $request->isSecure(),
            'use_strict_mode' => true,
            'use_only_cookies' => true,
        ]);
    }
}
