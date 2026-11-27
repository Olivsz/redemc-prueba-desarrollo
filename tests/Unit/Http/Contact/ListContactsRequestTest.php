<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Contact;

use App\Http\Contact\ListContactsRequest;
use App\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ListContactsRequestTest extends TestCase
{
    #[Test]
    public function readsTheRequestedPage(): void
    {
        $input = ListContactsRequest::fromRequest(new Request('GET', '/api/contacts', query: ['page' => '3']));

        self::assertSame(3, $input->page);
    }

    #[Test]
    public function requestsTheFirstPageWhenNoPageIsGiven(): void
    {
        $input = ListContactsRequest::fromRequest(new Request('GET', '/api/contacts'));

        self::assertSame(1, $input->page);
    }
}
