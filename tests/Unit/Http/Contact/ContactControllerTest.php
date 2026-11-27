<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Contact;

use App\Application\ListContacts;
use App\Application\RegisterContact;
use App\Domain\Contact\ContactValidationException;
use App\Domain\Contact\DuplicateContactException;
use App\Domain\Contact\NewContact;
use App\Http\Contact\ContactController;
use App\Http\Request;
use App\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryContactRepository;

final class ContactControllerTest extends TestCase
{
    private InMemoryContactRepository $contacts;
    private ContactController $controller;

    protected function setUp(): void
    {
        $this->contacts = new InMemoryContactRepository();
        $this->controller = new ContactController(
            new ListContacts($this->contacts),
            new RegisterContact($this->contacts),
        );
    }

    #[Test]
    public function listsTheRequestedPage(): void
    {
        $this->addContacts(10);

        $response = $this->controller->list(new Request('GET', '/api/contacts', query: ['page' => '2']));

        self::assertSame(200, $response->status);
        self::assertCount(2, $this->bodyOf($response)['data']);
        self::assertSame(2, $this->bodyOf($response)['meta']['page']);
    }

    #[Test]
    public function createsAContact(): void
    {
        $response = $this->controller->create($this->post('{"name": "Ana", "email": "ana@mail.com", "phone": ""}'));

        self::assertSame(201, $response->status);
        self::assertSame('Ana', $this->bodyOf($response)['data']['name']);
        self::assertSame(1, $this->contacts->countAll());
    }

    #[Test]
    public function letsTheValidationErrorsReachTheExceptionHandler(): void
    {
        $this->expectException(ContactValidationException::class);

        $this->controller->create($this->post('{"name": "", "email": "ana@mail.com"}'));
    }

    #[Test]
    public function letsTheDuplicatesReachTheExceptionHandler(): void
    {
        $this->controller->create($this->post('{"name": "Ana", "email": "ana@mail.com"}'));

        $this->expectException(DuplicateContactException::class);

        $this->controller->create($this->post('{"name": "Otra Ana", "email": "ana@mail.com"}'));
    }

    private function post(string $body): Request
    {
        return new Request('POST', '/api/contacts', body: $body);
    }

    private function bodyOf(Response $response): array
    {
        return json_decode($response->body, true);
    }

    private function addContacts(int $amount): void
    {
        foreach (array_slice(range('a', 'z'), 0, $amount) as $letter) {
            $this->contacts->add(NewContact::fromInput("Contacto {$letter}", "{$letter}@mail.com", ''));
        }
    }
}
