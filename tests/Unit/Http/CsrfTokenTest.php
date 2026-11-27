<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Http\CsrfToken;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CsrfTokenTest extends TestCase
{
    private CsrfToken $csrfToken;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->csrfToken = new CsrfToken();
    }

    #[Test]
    public function generatesALongRandomTokenThatStaysTheSameDuringTheSession(): void
    {
        $token = $this->csrfToken->value();

        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token);
        self::assertSame($token, $this->csrfToken->value());
    }

    #[Test]
    public function generatesADifferentTokenForEachSession(): void
    {
        $firstSessionToken = $this->csrfToken->value();
        $_SESSION = [];

        self::assertNotSame($firstSessionToken, $this->csrfToken->value());
    }

    #[Test]
    public function acceptsTheTokenOfTheSession(): void
    {
        self::assertTrue($this->csrfToken->isValid($this->csrfToken->value()));
    }

    #[Test]
    public function rejectsAWrongOrMissingToken(): void
    {
        $this->csrfToken->value();

        self::assertFalse($this->csrfToken->isValid('otro-token'));
        self::assertFalse($this->csrfToken->isValid(null));
    }

    #[Test]
    public function rejectsAnyTokenWhenTheSessionHasNone(): void
    {
        self::assertFalse($this->csrfToken->isValid('cualquier-token'));
    }
}
