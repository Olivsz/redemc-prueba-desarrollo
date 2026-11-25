#!/bin/sh
# Arranque del contenedor en producción (Railway).
set -e

# En Railway, Apache a veces arranca con más de un MPM habilitado y se niega a iniciar
# ("More than one MPM loaded"). PHP necesita prefork, así que se deja solo ese.
a2dismod -q mpm_event mpm_worker || true
a2enmod -q mpm_prefork

# Crea las tablas que falten. Si ya existen, no hace nada.
php bin/migrate.php

exec apache2-foreground
