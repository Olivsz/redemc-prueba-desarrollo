<?php

declare(strict_types=1);

namespace App\Http\Contact;

use App\Application\ContactPage;
use JsonSerializable;

final readonly class ContactPageResponse implements JsonSerializable
{
    private function __construct(
        public array $data,
        public array $meta,
    ) {
    }

    public static function fromPage(ContactPage $page): self
    {
        return new self(
            data: array_map(ContactResponse::fromContact(...), $page->contacts),
            meta: [
                'page' => $page->page,
                'totalPages' => $page->totalPages,
                'total' => $page->total,
                'pageSize' => $page->pageSize,
            ],
        );
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
