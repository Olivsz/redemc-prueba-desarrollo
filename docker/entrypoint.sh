#!/bin/sh
# Arranque del contenedor en producción (Railway).
set -e

# Railway no respeta los módulos que se apagan durante el build: vuelven a aparecer al ejecutar.
# Por eso se configuran acá, al arrancar.

# Con más de un MPM habilitado, Apache se niega a iniciar ("More than one MPM loaded").
# PHP necesita prefork, así que se deja solo ese.
a2dismod -q mpm_event mpm_worker || true
a2enmod -q mpm_prefork

# Módulos que la app no usa y que delatan a Apache: alias publica /icons/ y status, /server-status.
a2dismod -f -q alias autoindex status

# Crea las tablas que falten. Si ya existen, no hace nada.
php bin/migrate.php

exec apache2-foreground
