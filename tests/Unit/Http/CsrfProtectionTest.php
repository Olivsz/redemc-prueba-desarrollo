<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Http\CsrfProtection;
use App\Http\CsrfToken;
use App\Http\InvalidCsrfTokenException;
use App\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CsrfProtectionTest extends TestCase
{
    private CsrfToken $csrfToken;
    private CsrfProtection $protection;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->csrfToken = new CsrfToken();
        $this->protection = new CsrfProtection($this->csrfToken);
    }

    #[Test]
    public function letsReadingRequestsThroughWithoutAToken(): void
    {
        $this->protection->ensureIsAllowed(new Request('GET', '/api/contacts'));

        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function letsAChangeThroughWithTheTokenOfTheSession(): void
    {
        $headers = [CsrfToken::HEADER => $this->csrfToken->value()];

        $this->protection->ensureIsAllowed(new Request('POST', '/api/contacts', headers: $headers));

        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function blocksAChangeWithoutAToken(): void
    {
        $this->csrfToken->value();

        $this->expectException(InvalidCsrfTokenException::class);

        $this->protection->ensureIsAllowed(new Request('POST', '/api/contacts'));
    }

    #[Test]
    public function blocksAChangeWithAForgedToken(): void
    {
        $this->csrfToken->value();

        $this->expectException(InvalidCsrfTokenException::class);

        $this->protection->ensureIsAllowed(new Request('POST', '/api/contacts', headers: [CsrfToken::HEADER => 'falso']));
    }
}
