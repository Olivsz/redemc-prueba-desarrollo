<?php

declare(strict_types=1);

namespace App\Application;

final readonly class ContactPage
{
    public function __construct(
        public array $contacts,
        public int $page,
        public int $totalPages,
        public int $total,
        public int $pageSize,
    ) {
    }
}
