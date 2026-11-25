# RedEMC · Prueba de desarrollo

Formulario de contactos (nombre, email y teléfono) que guarda por Ajax en MySQL, con un listado paginado que muestra primero el registro más reciente.

**Stack:** PHP 8.4, MySQL 8.4, HTML, CSS, Bootstrap, JavaScript y jQuery.

## Requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/)

No hace falta instalar PHP, MySQL ni Composer: todo corre dentro de contenedores.

## Cómo correrlo en local

```bash
cp .env.example .env              # completar las claves
docker compose up -d --build
docker compose exec app composer install
```

La aplicación queda en <http://localhost:8080>.

## Tests

```bash
docker compose exec app composer test
```

## Estructura

```
public/     Punto de entrada web (único directorio expuesto)
src/        Código de la aplicación
tests/      Tests automatizados (PHPUnit)
docker/     Configuración de Apache y PHP
```

## Deploy en Railway

_Pendiente._
