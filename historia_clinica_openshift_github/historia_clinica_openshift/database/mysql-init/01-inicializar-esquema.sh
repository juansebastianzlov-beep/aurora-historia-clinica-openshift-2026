#!/bin/bash
# La imagen SCLorg ejecuta estos scripts con el servidor MySQL local disponible.
# Se carga el esquema solo cuando MySQL crea por primera vez un directorio vacío.
if [ "${MYSQL_DATADIR_FIRST_INIT:-false}" = "true" ]; then
  directorio_script="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
  mysql $mysql_flags < "${directorio_script}/historia_clinica.sql"
fi
