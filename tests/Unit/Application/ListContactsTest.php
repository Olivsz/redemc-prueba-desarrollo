<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use App\Application\ListContacts;
use App\Domain\Contact\Contact;
use App\Domain\Contact\NewContact;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryContactRepository;

final class ListContactsTest extends TestCase
{
    private InMemoryContactRepository $contacts;
    private ListContacts $listContacts;

    protected function setUp(): void
    {
        $this->contacts = new InMemoryContactRepository();
        $this->listContacts = new ListContacts($this->contacts);
    }

    #[Test]
    public function listsTheNewestContactsFirst(): void
    {
        $this->addContacts(3);

        $page = $this->listContacts->execute(1);

        self::assertSame(['Contacto c', 'Contacto b', 'Contacto a'], $this->namesOf($page->contacts));
    }

    #[Test]
    public function showsAtMostEightContactsPerPage(): void
    {
        $this->addContacts(10);

        $firstPage = $this->listContacts->execute(1);
        $secondPage = $this->listContacts->execute(2);

        self::assertCount(8, $firstPage->contacts);
        self::assertSame(['Contacto b', 'Contacto a'], $this->namesOf($secondPage->contacts));
        self::assertSame(2, $firstPage->totalPages);
        self::assertSame(10, $firstPage->total);
    }

    #[Test]
    public function returnsTheFirstPageWhenThePageIsBelowOne(): void
    {
        $this->addContacts(10);

        self::assertSame(1, $this->listContacts->execute(0)->page);
    }

    #[Test]
    public function returnsTheLastPageWhenThePageIsBeyondTheEnd(): void
    {
        $this->addContacts(10);

        self::assertSame(2, $this->listContacts->execute(99)->page);
    }

    #[Test]
    public function anEmptyListHasASinglePage(): void
    {
        $page = $this->listContacts->execute(1);

        self::assertSame([], $page->contacts);
        self::assertSame(1, $page->page);
        self::assertSame(1, $page->totalPages);
        self::assertSame(0, $page->total);
    }

    private function addContacts(int $amount): void
    {
        foreach (array_slice(range('a', 'z'), 0, $amount) as $letter) {
            $this->contacts->add(NewContact::fromInput("Contacto {$letter}", "{$letter}@mail.com", ''));
        }
    }

    private function namesOf(array $contacts): array
    {
        return array_map(fn (Contact $contact) => $contact->name->value, $contacts);
    }
}
