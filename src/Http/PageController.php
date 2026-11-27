<?php

declare(strict_types=1);

namespace App\Http;

final readonly class PageController
{
    public function __construct(
        private CsrfToken $csrfToken,
        private TemplateRenderer $templates,
    ) {
    }

    public function home(Request $request): Response
    {
        return Response::html(
            $this->templates->render('home.php', ['csrfToken' => $this->csrfToken->value()]),
        );
    }
}
