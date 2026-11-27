<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Contact\Contact;
use App\Domain\Contact\ContactField;
use App\Domain\Contact\ContactRepository;
use App\Domain\Contact\ContactValidationException;
use App\Domain\Contact\DuplicateContactException;
use App\Domain\Contact\NewContact;

final readonly class RegisterContact
{
    public function __construct(private ContactRepository $contacts)
    {
    }

    public function execute(string $name, string $email, string $phone): Contact
    {
        $contact = NewContact::fromInput($name, $email, $phone);

        $this->ensureIsNotRegistered($contact);

        return $this->save($contact);
    }

    private function ensureIsNotRegistered(NewContact $contact): void
    {
        $duplicatedFields = $this->duplicatedFieldsOf($contact);

        if ($duplicatedFields !== []) {
            throw ContactValidationException::forDuplicates(...$duplicatedFields);
        }
    }

    private function duplicatedFieldsOf(NewContact $contact): array
    {
        $fields = [];

        if ($this->isEmailRegistered($contact)) {
            $fields[] = ContactField::Email;
        }

        if ($this->isPhoneRegistered($contact)) {
            $fields[] = ContactField::Phone;
        }

        return $fields;
    }

    private function isEmailRegistered(NewContact $contact): bool
    {
        return $this->contacts->existsWithEmail($contact->email);
    }

    private function isPhoneRegistered(NewContact $contact): bool
    {
        return $contact->phone !== null && $this->contacts->existsWithPhone($contact->phone);
    }

    private function save(NewContact $contact): Contact
    {
        try {
            return $this->contacts->add($contact);
        } catch (DuplicateContactException $exception) {
            throw ContactValidationException::forDuplicates($exception->field);
        }
    }
}
