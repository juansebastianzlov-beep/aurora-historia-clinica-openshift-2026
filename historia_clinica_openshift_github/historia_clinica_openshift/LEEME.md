# Aurora · Historia Clínica (demostración académica)

Aplicación local de ejemplo para practicar PHP, MySQL y relaciones entre tablas. **Todos los nombres, documentos, síntomas, consultas, diagnósticos, tratamientos y datos de contacto incluidos son ficticios.** No ingresar información real ni usar como herramienta de atención médica.

## Incluye

- Inicio con indicadores y accesos rápidos.
- Registro de personas e historias clínicas, con tipo de sangre, motivo de consulta, síntomas, afectaciones, valoración, diagnóstico/categoría, tratamientos y notas.
- Consulta del historial por tipo y número de documento. Una persona puede tener varias consultas.
- Búsqueda y modificación de datos de la persona y de cada historia. Los cambios se guardan en MySQL.
- 12 tablas relacionadas mediante claves foráneas.
- Sesión de acceso, formularios protegidos contra CSRF, consultas preparadas y salida escapada.

## Instalación en XAMPP para Windows

1. Abre el panel de XAMPP e inicia **Apache** y **MySQL**.
2. Descomprime `historia_clinica_xampp.zip` y copia la carpeta `historia_clinica_xampp` a `C:\xampp\htdocs\`.
3. Abre `http://localhost/phpmyadmin`.
4. En phpMyAdmin, abre la pestaña **Importar**, selecciona `historia_clinica.sql` y pulsa **Importar**. El SQL crea la base `historia_clinica_demostracion`. Importar el archivo de nuevo elimina y vuelve a crear las tablas de esa base.
5. Abre `http://localhost/historia_clinica_xampp/`.
6. Ingresa con el acceso de práctica indicado abajo.

La configuración predeterminada espera MySQL en `127.0.0.1`, usuario `root` y contraseña vacía, que es la configuración usual de XAMPP local. Si cambiaste esos valores, edita `componentes/configuracion.php`.

## Acceso inicial de práctica

- Correo: `acceso@demostracion.local`
- Contraseña: `Clave1234!`

Es una contraseña fija para la demostración local. Puedes actualizar el usuario y su clave en la tabla `usuarios_aplicacion` antes de compartir el proyecto.

## Probar las pantallas

- **Consultar:** el registro ficticio `OTRO / PRUEBA-1001` tiene dos historias para comprobar que la búsqueda agrupa varias consultas.
- **Modificar:** desde el menú, busca el mismo documento y elige una historia. Al guardar, consulta de nuevo el registro para ver los cambios persistidos.
- **Nuevo registro:** prueba con un documento de demostración nuevo, por ejemplo `PRUEBA-1004`.

## Modelo de datos

El esquema está normalizado en estas 12 tablas: `usuarios_aplicacion`, `tipos_sangre`, `pacientes`, `historias_clinicas`, `diagnosticos`, `historia_diagnosticos`, `tratamientos`, `historia_tratamientos`, `alergias`, `paciente_alergias`, `medicamentos` y `formulas_medicas`.

## Alcance

Este proyecto es una maqueta universitaria local. No implementa cifrado de datos clínicos, perfiles clínicos certificados, copias de seguridad, auditoría legal, despliegue seguro, consentimiento informado ni controles exigibles a un sistema real. No publicar en internet ni utilizar para pacientes reales.
