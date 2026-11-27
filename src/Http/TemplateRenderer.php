<?php

declare(strict_types=1);

namespace App\Http;

final readonly class TemplateRenderer
{
    public function __construct(private string $directory)
    {
    }

    public function render(string $template, array $variables): string
    {
        extract($variables, EXTR_SKIP);

        ob_start();
        require "{$this->directory}/{$template}";

        return (string) ob_get_clean();
    }
}
