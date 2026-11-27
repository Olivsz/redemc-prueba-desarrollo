<?php

declare(strict_types=1);

namespace App\Http;

use Throwable;

final readonly class HttpKernel
{
    public function __construct(
        private Router $router,
        private CsrfProtection $csrfProtection,
        private ExceptionHandler $exceptionHandler,
    ) {
    }

    public function handle(Request $request): Response
    {
        try {
            return $this->process($request);
        } catch (Throwable $error) {
            return $this->exceptionHandler->toResponse($error);
        }
    }

    private function process(Request $request): Response
    {
        $handler = $this->router->match($request);

        $this->csrfProtection->ensureIsAllowed($request);

        return $handler($request);
    }
}
