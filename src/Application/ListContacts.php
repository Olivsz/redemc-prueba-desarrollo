<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Contact\ContactRepository;

final readonly class ListContacts
{
    public const int PAGE_SIZE = 8;

    public function __construct(private ContactRepository $contacts)
    {
    }

    public function execute(int $requestedPage): ContactPage
    {
        $total = $this->contacts->countAll();
        $totalPages = $this->totalPagesFor($total);
        $page = $this->keepWithinRange($requestedPage, $totalPages);

        return new ContactPage(
            contacts: $this->contacts->findLatest(self::PAGE_SIZE, $this->offsetOf($page)),
            page: $page,
            totalPages: $totalPages,
            total: $total,
        );
    }

    private function totalPagesFor(int $total): int
    {
        return max(1, (int) ceil($total / self::PAGE_SIZE));
    }

    private function keepWithinRange(int $page, int $totalPages): int
    {
        return min(max($page, 1), $totalPages);
    }

    private function offsetOf(int $page): int
    {
        return ($page - 1) * self::PAGE_SIZE;
    }
}
