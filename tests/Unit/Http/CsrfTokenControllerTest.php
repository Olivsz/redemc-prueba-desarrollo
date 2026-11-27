<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Http\CsrfToken;
use App\Http\CsrfTokenController;
use App\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CsrfTokenControllerTest extends TestCase
{
    #[Test]
    public function answersTheCsrfTokenOfTheSessionWithoutLettingItBeCached(): void
    {
        $_SESSION = [];
        $csrfToken = new CsrfToken();
        $controller = new CsrfTokenController($csrfToken);

        $response = $controller->show(new Request('GET', '/api/csrf-token'));

        self::assertSame(200, $response->status);
        self::assertSame(['token' => $csrfToken->value()], json_decode($response->body, true));
        self::assertSame('no-store', $response->headers['Cache-Control']);
    }
}
