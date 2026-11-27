<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Http\CsrfToken;
use App\Http\PageController;
use App\Http\Request;
use App\Http\TemplateRenderer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PageControllerTest extends TestCase
{
    #[Test]
    public function rendersTheHomePageWithTheCsrfTokenOfTheSession(): void
    {
        $_SESSION = [];
        $csrfToken = new CsrfToken();
        $controller = new PageController($csrfToken, new TemplateRenderer(dirname(__DIR__, 3) . '/templates'));

        $response = $controller->home(new Request('GET', '/'));

        self::assertSame(200, $response->status);
        self::assertStringStartsWith('text/html', $response->headers['Content-Type']);
        self::assertStringContainsString(
            '<meta name="csrf-token" content="' . $csrfToken->value() . '">',
            $response->body,
        );
    }
}
