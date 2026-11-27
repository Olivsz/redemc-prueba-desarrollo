<?php

declare(strict_types=1);

namespace App\Domain\Contact;

final readonly class NewContact
{
    private function __construct(
        public PersonName $name,
        public Email $email,
        public ?UruguayanMobilePhone $phone,
    ) {
    }

    public static function fromInput(string $name, string $email, string $phone): self
    {
        $errors = new ContactDataErrors();

        $validName = $errors->capture(ContactField::Name, fn () => PersonName::fromString($name));
        $validEmail = $errors->capture(ContactField::Email, fn () => Email::fromString($email));
        $validPhone = $errors->capture(ContactField::Phone, fn () => UruguayanMobilePhone::fromOptionalString($phone));

        $errors->throwIfAny();

        return new self($validName, $validEmail, $validPhone);
    }
}
