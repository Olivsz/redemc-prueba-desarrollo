<?php

declare(strict_types=1);

namespace App\Http\Contact;

use App\Http\Request;

final readonly class ListContactsRequest
{
    private const int FIRST_PAGE = 1;

    private function __construct(public int $page)
    {
    }

    public static function fromRequest(Request $request): self
    {
        return new self((int) ($request->query('page') ?? self::FIRST_PAGE));
    }
}
