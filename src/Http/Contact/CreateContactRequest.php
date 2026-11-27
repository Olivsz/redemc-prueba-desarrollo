<?php

declare(strict_types=1);

namespace App\Http\Contact;

use App\Http\InvalidRequestException;
use App\Http\Request;

final readonly class CreateContactRequest
{
    private function __construct(
        public string $name,
        public string $email,
        public string $phone,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $body = $request->jsonBody();

        return new self(
            name: self::textField($body, 'name'),
            email: self::textField($body, 'email'),
            phone: self::textField($body, 'phone'),
        );
    }

    private static function textField(array $body, string $field): string
    {
        $value = $body[$field] ?? '';

        self::ensureIsText($value);

        return (string) $value;
    }

    private static function ensureIsText(mixed $value): void
    {
        if (!is_scalar($value)) {
            throw new InvalidRequestException();
        }
    }
}
