# RedEMC · Prueba de desarrollo

Formulario de contactos (nombre, email y teléfono) que guarda por Ajax en MySQL, con un listado paginado que muestra primero el registro más reciente.

**Aplicación publicada:** <https://redemc-prueba-desarrollo-production.up.railway.app/>

## Qué hace

- **Formulario:** valida en el navegador al salir de cada campo y, de nuevo, en el servidor, que es la validación que manda. Cada error aparece debajo de su campo sin borrar lo que ya se cargó.
  - **Nombre:** obligatorio; solo letras (con tildes, ñ y ü) y espacios. Un nombre hecho solo de espacios cuenta como vacío.
  - **Email:** obligatorio, con formato válido y único sin distinguir mayúsculas.
  - **Teléfono:** opcional; si se completa, tiene que ser un celular uruguayo de 9 dígitos que empiece con `09`, sin espacios, guiones ni `+598`. Es único.
- **Listado:** 8 contactos por página, del más reciente al más antiguo, con paginado por Ajax. Al guardar, el contacto nuevo aparece primero y resaltado, sin recargar la página.

La estructura de la tabla está en [`database/schema.sql`](database/schema.sql).

## Stack

PHP 8.4 (sin framework), MySQL 8.4 y Apache; HTML, CSS, Bootstrap 5.3, JavaScript (módulos) y jQuery 3.7. Todo corre en Docker. Tests con PHPUnit y con el runner de tests de Node.

## Arquitectura

El código PHP está organizado en capas. Las dependencias apuntan hacia adentro: el dominio no depende de nada, y la base de datos queda detrás de una interfaz.

```
src/
├── Domain/             Reglas del negocio: Value Objects (nombre, email, teléfono),
│                       la entidad Contact y la interfaz ContactRepository
├── Application/        Casos de uso: RegisterContact y ListContacts
├── Infrastructure/     MySQL con PDO: el repositorio, la conexión y la migración
├── Http/               Pipeline HTTP propio: router, kernel, CSRF, manejo de errores,
│                       controladores y DTOs de entrada y salida
└── CompositionRoot.php Arma y conecta los objetos: es el único que conoce todas las capas

public/      Punto de entrada (index.php) y archivos estáticos: lo único expuesto a la web
templates/   HTML de la página
database/    schema.sql
docker/      Configuración de Apache y PHP, y el script de arranque
tests/       PHPUnit (unitarios y de integración) y tests de JavaScript
```

Cada pedido pasa por el mismo camino: el router encuentra la ruta, se valida el token CSRF y el controlador ejecuta tres pasos (DTO de entrada, caso de uso y DTO de salida). Cualquier error sube hasta el kernel, que lo traduce a un código HTTP en un solo lugar.

| Método | Ruta | Respuesta |
|---|---|---|
| `GET` | `/api/contacts?page=N` | Página de contactos: `data` y `meta` (`page`, `totalPages`, `total`, `pageSize`) |
| `POST` | `/api/contacts` | Crea un contacto. Recibe JSON y el header `X-CSRF-Token`. Responde 201, 400 (pedido ilegible), 403 (token inválido), 409 (email o teléfono repetido) o 422 (datos inválidos) |
| `GET` | `/api/csrf-token` | Un token nuevo, para reintentar cuando la sesión venció |

## Cómo correrlo en local

Requisitos: [Docker Desktop](https://www.docker.com/products/docker-desktop/). No hace falta instalar PHP, MySQL ni Composer.

```bash
cp .env.example .env              # completar las claves
docker compose up -d --build
docker compose exec app composer install
```

La aplicación queda en <http://localhost:8080>. La primera vez, MySQL crea la tabla a partir de `database/schema.sql`.

## Tests

```bash
docker compose exec app composer test   # PHP: unitarios e integración contra el MySQL del contenedor
npm test                                # JavaScript: validaciones, paginado y formato (requiere Node 22 o superior)
```

Los tests de integración corren dentro de transacciones que se deshacen, así que no dejan datos en la base.

## Cómo lo publiqué en Railway

La aplicación y la base de datos están en el mismo proyecto de Railway.

1. **Proyecto:** *New Project* → *Deploy from GitHub repo* → este repositorio. Railway detecta el `Dockerfile` y construye su última etapa, `prod`: PHP con Apache, solo las dependencias de producción y los errores ocultos.
2. **Base de datos:** en el mismo proyecto, *New* → *Database* → *MySQL*. Queda privada: solo la aplicación la alcanza, por la red interna de Railway.
3. **Variables:** en el servicio de la aplicación (no en el de MySQL ni como *Shared Variables*), se cargan como referencias a las del servicio MySQL:

   | Variable | Valor |
   |---|---|
   | `DB_HOST` | `${{MySQL.MYSQLHOST}}` |
   | `DB_PORT` | `${{MySQL.MYSQLPORT}}` |
   | `DB_NAME` | `${{MySQL.MYSQLDATABASE}}` |
   | `DB_USER` | `${{MySQL.MYSQLUSER}}` |
   | `DB_PASSWORD` | `${{MySQL.MYSQLPASSWORD}}` |

   Son referencias, no valores: Railway las reemplaza por los datos reales al ejecutar, dentro del proyecto. Las claves nunca están en el repositorio: el `.env` local está en `.gitignore` y `.env.example` solo tiene valores de ejemplo.

   Railway propone *Suggested Variables* a partir de `.env.example`, pero son los valores de Docker local y no hay que aceptarlas.
4. **Rama:** *Settings* → *Source* → `main`. Durante el desarrollo publicaba `develop`, que hizo de entorno de pruebas.
5. **Dominio público:** *Settings* → *Networking* → *Generate Domain*, con el puerto 8080. Railway le indica el puerto a la aplicación con la variable `PORT`, y Apache la lee al arrancar.
6. **Deploy.** Al arrancar, el contenedor ejecuta [`docker/entrypoint.sh`](docker/entrypoint.sh):
   - deja un solo MPM de Apache y apaga los módulos que no se usan;
   - ejecuta `bin/migrate.php`, que crea la tabla si no existe. Railway no tiene una consola SQL, así que la base se prepara sola, sin pasos manuales y sin exponerla a internet;
   - inicia Apache.
7. **Prueba:** abrir el link, guardar un contacto y ver que aparezca primero en el listado.

### Problemas que aparecieron

- **`AH00534: More than one MPM loaded`.** Apache arrancaba con dos MPM habilitados y se caía. Railway no respeta los módulos que se apagan durante el build: vuelven a aparecer al ejecutar. Por eso el script de arranque los configura, y lo mismo hace con los módulos `alias`, `autoindex` y `status`, que exponían `/icons/` y `/server-status`.
- **`Missing required environment variable DB_HOST`.** Las variables estaban cargadas como *Shared Variables* del proyecto, que ningún servicio recibe salvo que se compartan. Se cargaron en el servicio de la aplicación. La aplicación valida su configuración al arrancar, así que el error señaló el problema exacto.
- **Error de *fetch* al crear el proyecto.** La rama por defecto del repositorio (`main`) todavía estaba vacía; se resolvió usando `develop` como rama por defecto durante el desarrollo.

## Seguridad

- **Inyección SQL:** consultas preparadas reales (`PDO::ATTR_EMULATE_PREPARES` desactivado).
- **XSS:** los datos se validan por lista blanca, el listado se arma con texto (nunca con HTML) y una *Content-Security-Policy* solo permite los scripts propios y los archivos exactos de los CDN, que además se cargan con *Subresource Integrity*.
- **CSRF:** token por sesión en el header `X-CSRF-Token`, comparado con `hash_equals`; la cookie de sesión es `HttpOnly`, `Secure` y `SameSite=Strict`. Si la sesión vence (por ejemplo, después de una nueva publicación), el formulario pide un token nuevo y reintenta solo.
- **Repetidos con envíos simultáneos:** además de la consulta previa, índices únicos en la base, que frenan el segundo envío aunque lleguen a la vez.
- **Headers:** HSTS, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` y `Permissions-Policy`.
- **Límites:** pedidos de hasta 16 KB, hasta 30 procesos de Apache y 30 segundos de *timeout*.
- **Errores:** el usuario ve un mensaje genérico y el detalle va al log. Apache no informa su versión, y solo `public/` es accesible desde la web.

La aplicación publicada se sometió a una prueba de penetración: inyecciones, CSRF, Unicode engañoso, tipos inesperados en el JSON, envíos simultáneos, cuerpos enormes y archivos expuestos.

## Limitaciones conocidas y mejoras futuras

- **Límite de envíos:** hoy un script puede cargar muchos contactos seguidos. En producción agregaría un límite por IP (respuesta 429) y un WAF o CDN, como Cloudflare, delante de la aplicación.
- **Datos públicos:** el listado y la API muestran los emails y teléfonos de todos, como pide la prueba. En un sistema real harían falta usuarios y autenticación, o mostrar los datos enmascarados.
- **Sesiones:** se guardan en archivos dentro del contenedor y se pierden en cada publicación (el reintento automático lo cubre). Con más de una instancia harían falta sesiones compartidas, por ejemplo en Redis.
- **Paginado:** usa `OFFSET`, que se vuelve lento con cientos de miles de registros. A esa escala convendría paginar por clave ("los anteriores al id X").
- **Reglas de capas:** la dirección de las dependencias se respeta por disciplina. Una herramienta como Deptrac podría verificarla en cada build.
