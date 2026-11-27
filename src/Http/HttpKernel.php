<?php

declare(strict_types=1);

namespace App\Http;

use App\Application\ListContacts;
use App\Application\RegisterContact;
use App\Http\Contact\ContactController;
use App\Infrastructure\Persistence\DatabaseConfig;
use App\Infrastructure\Persistence\MySqlConnectionFactory;
use App\Infrastructure\Persistence\PdoContactRepository;
use Throwable;

final readonly class HttpKernel
{
    public function __construct(
        private Router $router,
        private CsrfProtection $csrfProtection,
        private ExceptionHandler $exceptionHandler,
    ) {
    }

    public static function create(array $environment): self
    {
        $csrfToken = new CsrfToken();
        $router = new Router();

        $router->get('/', fn (Request $request) => self::pageController($csrfToken)->home($request));
        $router->get('/api/contacts', fn (Request $request) => self::contactController($environment)->list($request));
        $router->post('/api/contacts', fn (Request $request) => self::contactController($environment)->create($request));

        return new self(
            $router,
            new CsrfProtection($csrfToken),
            new ExceptionHandler(fn (Throwable $error) => error_log((string) $error)),
        );
    }

    public function handle(Request $request): Response
    {
        try {
            return $this->process($request);
        } catch (Throwable $error) {
            return $this->exceptionHandler->toResponse($error);
        }
    }

    private function process(Request $request): Response
    {
        $this->csrfProtection->ensureIsAllowed($request);

        return $this->router->dispatch($request);
    }

    private static function pageController(CsrfToken $csrfToken): PageController
    {
        return new PageController($csrfToken, new TemplateRenderer(dirname(__DIR__, 2) . '/templates'));
    }

    private static function contactController(array $environment): ContactController
    {
        $connection = MySqlConnectionFactory::create(DatabaseConfig::fromEnvironment($environment));
        $contacts = new PdoContactRepository($connection);

        return new ContactController(new ListContacts($contacts), new RegisterContact($contacts));
    }
}
