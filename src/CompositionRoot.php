<?php

declare(strict_types=1);

namespace App;

use App\Application\ListContacts;
use App\Application\RegisterContact;
use App\Http\Contact\ContactController;
use App\Http\CsrfProtection;
use App\Http\CsrfToken;
use App\Http\CsrfTokenController;
use App\Http\ExceptionHandler;
use App\Http\HttpKernel;
use App\Http\PageController;
use App\Http\Request;
use App\Http\Router;
use App\Http\TemplateRenderer;
use App\Infrastructure\Persistence\DatabaseConfig;
use App\Infrastructure\Persistence\MySqlConnectionFactory;
use App\Infrastructure\Persistence\PdoContactRepository;
use Throwable;

/**
 * Arma los objetos de todas las capas y los conecta, como Program.cs en ASP.NET.
 * Es el único lugar que conoce la infraestructura: el resto de las capas no depende de ella.
 */
final class CompositionRoot
{
    public static function httpKernel(array $environment): HttpKernel
    {
        $csrfToken = new CsrfToken();

        return new HttpKernel(
            self::router($environment, $csrfToken),
            new CsrfProtection($csrfToken),
            new ExceptionHandler(fn (Throwable $error) => error_log((string) $error)),
        );
    }

    private static function router(array $environment, CsrfToken $csrfToken): Router
    {
        $router = new Router();

        $router->get('/', fn (Request $request) => self::pageController($csrfToken)->home($request));
        $router->get('/api/csrf-token', fn (Request $request) => (new CsrfTokenController($csrfToken))->show($request));
        $router->get('/api/contacts', fn (Request $request) => self::contactController($environment)->list($request));
        $router->post('/api/contacts', fn (Request $request) => self::contactController($environment)->create($request));

        return $router;
    }

    private static function pageController(CsrfToken $csrfToken): PageController
    {
        return new PageController($csrfToken, new TemplateRenderer(dirname(__DIR__) . '/templates'));
    }

    private static function contactController(array $environment): ContactController
    {
        $connection = MySqlConnectionFactory::create(DatabaseConfig::fromEnvironment($environment));
        $contacts = new PdoContactRepository($connection);

        return new ContactController(new ListContacts($contacts), new RegisterContact($contacts));
    }
}
