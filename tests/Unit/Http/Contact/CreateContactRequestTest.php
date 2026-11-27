<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Contact;

use App\Http\Contact\CreateContactRequest;
use App\Http\InvalidRequestException;
use App\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CreateContactRequestTest extends TestCase
{
    #[Test]
    public function readsTheFieldsOfTheJsonBody(): void
    {
        $input = CreateContactRequest::fromRequest($this->post('{"name": "Ana", "email": "ana@mail.com", "phone": "097680102"}'));

        self::assertSame('Ana', $input->name);
        self::assertSame('ana@mail.com', $input->email);
        self::assertSame('097680102', $input->phone);
    }

    #[Test]
    public function treatsMissingFieldsAsEmpty(): void
    {
        $input = CreateContactRequest::fromRequest($this->post('{}'));

        self::assertSame('', $input->name);
        self::assertSame('', $input->email);
        self::assertSame('', $input->phone);
    }

    #[Test]
    public function rejectsAFieldThatIsNotText(): void
    {
        $this->expectException(InvalidRequestException::class);

        CreateContactRequest::fromRequest($this->post('{"name": ["Ana"], "email": "ana@mail.com"}'));
    }

    #[Test]
    public function rejectsABodyThatIsNotJson(): void
    {
        $this->expectException(InvalidRequestException::class);

        CreateContactRequest::fromRequest($this->post('{"name": "Ana"'));
    }

    private function post(string $body): Request
    {
        return new Request('POST', '/api/contacts', body: $body);
    }
}
