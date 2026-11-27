<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Entrega un token nuevo cuando la sesión venció, por ejemplo después de una nueva publicación
 * del servidor, que borra las sesiones. Así el formulario puede reintentar sin recargar la página.
 * Otro sitio no puede leer la respuesta: el navegador se lo impide (no hay CORS).
 */
final readonly class CsrfTokenController
{
    public function __construct(private CsrfToken $csrfToken)
    {
    }

    public function show(Request $request): Response
    {
        return Response::json(200, ['token' => $this->csrfToken->value()]);
    }
}
