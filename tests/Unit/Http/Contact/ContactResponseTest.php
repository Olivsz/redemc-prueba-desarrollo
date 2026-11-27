<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Contact;

use App\Application\ContactPage;
use App\Domain\Contact\Contact;
use App\Domain\Contact\Email;
use App\Domain\Contact\PersonName;
use App\Domain\Contact\UruguayanMobilePhone;
use App\Http\Contact\ContactPageResponse;
use App\Http\Contact\ContactResponse;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ContactResponseTest extends TestCase
{
    #[Test]
    public function presentsAContactWithItsCreationDateInUtc(): void
    {
        $response = ContactResponse::fromContact($this->contact(phone: '097680102'));

        self::assertSame(
            [
                'id' => 7,
                'name' => 'Ana María',
                'email' => 'ana@mail.com',
                'phone' => '097680102',
                'createdAt' => '2026-11-27T20:15:00+00:00',
            ],
            $response->jsonSerialize(),
        );
    }

    #[Test]
    public function presentsAMissingPhoneAsNull(): void
    {
        $response = ContactResponse::fromContact($this->contact(phone: ''));

        self::assertNull($response->jsonSerialize()['phone']);
    }

    #[Test]
    public function presentsAPageWithItsPaginationData(): void
    {
        $page = new ContactPage([$this->contact(phone: '')], page: 2, totalPages: 3, total: 17);

        $json = json_decode(json_encode(ContactPageResponse::fromPage($page)), true);

        self::assertCount(1, $json['data']);
        self::assertSame('Ana María', $json['data'][0]['name']);
        self::assertSame(['page' => 2, 'totalPages' => 3, 'total' => 17, 'pageSize' => 8], $json['meta']);
    }

    private function contact(string $phone): Contact
    {
        return new Contact(
            id: 7,
            name: PersonName::fromString('Ana María'),
            email: Email::fromString('ana@mail.com'),
            phone: UruguayanMobilePhone::fromOptionalString($phone),
            createdAt: new DateTimeImmutable('2026-11-27 20:15:00', new DateTimeZone('UTC')),
        );
    }
}
