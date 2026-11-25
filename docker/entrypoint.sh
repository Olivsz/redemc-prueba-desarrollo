#!/bin/sh
# Arranque del contenedor en producción (Railway).
set -e

# En Railway, Apache a veces arranca con más de un MPM habilitado y se niega a iniciar
# ("More than one MPM loaded"). PHP necesita prefork, así que se deja solo ese.
a2dismod -q mpm_event mpm_worker || true
a2enmod -q mpm_prefork

exec apache2-foreground
