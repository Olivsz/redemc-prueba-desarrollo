<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * Espacios tal como los ve una persona: además del espacio común, cuenta los espacios Unicode,
 * como el espacio duro que aparece al copiar un nombre de Word o de una página web.
 * El trim() de JavaScript también los recorta, así el navegador y el servidor validan igual.
 */
final class Whitespace
{
    // Lo que recorta trim() de PHP (espacio, \t, \n, \r, \0 y \x0B) más los espacios Unicode (\p{Zs}).
    private const string AT_THE_EDGES = '/^[\p{Zs}\t\n\r\0\x0B]+|[\p{Zs}\t\n\r\0\x0B]+$/uD';
    private const string RUNS_OF_SPACES = '/\p{Zs}+/u';

    public static function trim(string $text): string
    {
        return self::replace(self::AT_THE_EDGES, '', $text);
    }

    public static function collapse(string $text): string
    {
        return self::trim(self::replace(self::RUNS_OF_SPACES, ' ', $text));
    }

    public static function isBlank(string $text): bool
    {
        return self::trim($text) === '';
    }

    // Con UTF-8 inválido, preg_replace devuelve null: el texto queda como está y la validación lo rechaza.
    private static function replace(string $pattern, string $replacement, string $text): string
    {
        return preg_replace($pattern, $replacement, $text) ?? $text;
    }
}
