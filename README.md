# Emulador de Terminal y Panel de Administración Web

Interfaz web desarrollada en PHP que simula el comportamiento de una terminal de Linux, permitiendo la ejecución controlada de comandos de sistema, operaciones de archivos y gestión de procesos.

## Arquitectura y Entorno
* Backend: PHP 8.x
* Frontend: HTML, CSS, JavaScript
* Infraestructura: Diseñado para entornos Linux. Despliegue validado en VPS de DigitalOcean (Ubuntu/Debian).

## Módulos Principales
* Interfaz de Comandos (terminal.php): Consola interactiva que procesa instrucciones del sistema anfitrión aislando la entrada del usuario.
* Panel Administrativo (admin/dashboard.php): Dashboard para el monitoreo de recursos del servidor, estadísticas de acceso y control de usuarios.
* Autenticación (session_check.php): Gestión de sesiones seguras para restringir el acceso a los módulos críticos del sistema.

## Consideraciones de Seguridad y Despliegue
Este sistema actúa como un puente entre la web y el sistema operativo. Su implementación en producción exige la configuración estricta de permisos de ejecución en el usuario del servidor web (ej. `www-data`) y el uso de un firewall (UFW) para mitigar riesgos de escalamiento de privilegios o ejecución de código arbitrario.
