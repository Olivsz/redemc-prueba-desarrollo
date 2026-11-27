<?php

declare(strict_types=1);

namespace App\Http;

final readonly class CsrfProtection
{
    private const array READ_ONLY_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function __construct(private CsrfToken $csrfToken)
    {
    }

    public function ensureIsAllowed(Request $request): void
    {
        if ($this->isForged($request)) {
            throw new InvalidCsrfTokenException();
        }
    }

    private function isForged(Request $request): bool
    {
        return $this->changesData($request)
            && !$this->csrfToken->isValid($request->header(CsrfToken::HEADER));
    }

    private function changesData(Request $request): bool
    {
        return !in_array($request->method, self::READ_ONLY_METHODS, true);
    }
}
