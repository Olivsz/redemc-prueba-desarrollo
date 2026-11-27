<?php

declare(strict_types=1);

namespace App\Http\Contact;

use App\Application\ListContacts;
use App\Application\RegisterContact;
use App\Http\Request;
use App\Http\Response;

final readonly class ContactController
{
    public function __construct(
        private ListContacts $listContacts,
        private RegisterContact $registerContact,
    ) {
    }

    public function list(Request $request): Response
    {
        $input = ListContactsRequest::fromRequest($request);

        $page = $this->listContacts->execute($input->page);

        return Response::json(200, ContactPageResponse::fromPage($page));
    }

    public function create(Request $request): Response
    {
        $input = CreateContactRequest::fromRequest($request);

        $contact = $this->registerContact->execute($input->name, $input->email, $input->phone);

        return Response::json(201, ['data' => ContactResponse::fromContact($contact)]);
    }
}
