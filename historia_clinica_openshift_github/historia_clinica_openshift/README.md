# Aurora · Historia Clínica — XAMPP y OpenShift

Aplicación académica de práctica en PHP y MySQL. Los datos de demostración son ficticios. No ingresar información real ni usar para atención médica.

## Subir el proyecto a GitHub

1. Crea un repositorio vacío en GitHub.
2. Descomprime el ZIP y sube **los archivos y carpetas que contiene**, conservando esta estructura en la raíz del repositorio. No subas solo el ZIP cerrado: OpenShift necesita leer el código fuente.
3. Copia la URL HTTPS del repositorio, que termina en `.git`.

## Desplegar en OpenShift

1. Entra al proyecto (namespace) donde harás el despliegue.
2. Abre `openshift/historia-clinica-openshift.yaml` en un editor de texto. Cambia las dos apariciones de `https://github.com/USUARIO/REPOSITORIO.git` por la URL real de tu repositorio.
3. En el recurso `Secret`, reemplaza ambos valores `REEMPLAZAR_...` por contraseñas aleatorias largas. Conserva las contraseñas en privado: no las publiques en GitHub ni las compartas. Si el repositorio es público, no guardes el archivo ya editado dentro del repositorio.
4. En la consola web de OpenShift, con el proyecto correcto seleccionado, usa **Developer → +Add → Import YAML**. Pega el contenido completo del archivo editado y crea los recursos.
5. En **Builds**, espera a que terminen `construir-historia-clinica-web` y `construir-historia-clinica-mysql`. La primera compilación toma el código del repositorio; la segunda prepara MySQL y carga las tablas y registros ficticios al inicializar por primera vez el volumen vacío.
6. En **Topology**, espera a que las cargas de trabajo estén listas. En **Networking → Routes**, abre `ruta-historia-clinica`.

La aplicación se conecta al servicio interno `servicio-mysql`; la base de datos no tiene una ruta pública. El volumen persistente conserva la base si el pod se reinicia. La importación del SQL ocurre solo cuando MySQL inicializa un volumen vacío. Si se elimina y vuelve a crear el volumen, se cargan de nuevo los datos de demostración. Después de subir cambios al repositorio, inicia una compilación nueva desde **Builds**.

### Si algo no inicia

- Si el PVC queda `Pending`, el clúster puede no tener una clase de almacenamiento predeterminada o tu proyecto no tiene cuota disponible. Revisa el evento del PVC o solicita al profesor el nombre de la clase permitida.
- Si el build no puede leer el repositorio, comprueba que la URL sea correcta y que GitHub permita lectura desde OpenShift. Para un repositorio privado se debe configurar una credencial de origen Git en OpenShift.
- Si el clúster no permite `BuildConfig`, `DeploymentConfig` o `Route`, consulta al administrador o al profesor; algunos proyectos educativos restringen esos recursos.

## XAMPP local

La configuración sigue usando `127.0.0.1`, `root` y contraseña vacía si no se definen variables de entorno. Sigue los pasos de instalación de `LEEME.md`.

Acceso de práctica: `acceso@demostracion.local` / `Clave1234!`.

## Alcance y seguridad

Es una maqueta universitaria. No está preparada para historias clínicas reales: no implementa controles legales, auditoría clínica ni cifrado de datos clínicos. El Route ofrece HTTPS en el borde del clúster, pero eso no convierte la aplicación en un sistema clínico apto para producción.
