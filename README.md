
# Agenda Personal de Contactos

## Descripción

Aplicación web desarrollada como parte de la asignatura
Desarrollo Web de la Universidad Técnica Particular de Loja (UTPL).

El sistema permite registrar usuarios y administrar una agenda
personal de contactos mediante una interfaz web.

Cada usuario puede consultar y gestionar únicamente sus propios
contactos, utilizando un sistema de autenticación basado en sesiones.

## Tecnologías utilizadas

- HTML5
- PHP 8
- MySQL / MariaDB
- PDO para la conexión con la base de datos
- JavaScript para confirmar la eliminación de contactos
- XAMPP como entorno de desarrollo local
- Git para el control de versiones

## Funcionalidades

- Registro de usuarios con correo electrónico único.
- Almacenamiento seguro de contraseñas mediante password_hash().
- Inicio de sesión mediante password_verify().
- Protección de páginas privadas mediante sesiones PHP.
- Visualización de contactos del usuario autenticado.
- Registro de nuevos contactos.
- Edición de contactos existentes.
- Eliminación de contactos con confirmación.
- Validación de formularios en el servidor.
- Consultas preparadas para trabajar con MySQL.
- Verificación de propiedad antes de editar o eliminar contactos.
- Protección CSRF en operaciones sensibles.
- Cierre seguro de sesión.

## Requisitos

Para ejecutar el proyecto se necesita:

- Servidor web Apache.
- PHP 8 con extensiones PDO y pdo_mysql.
- MySQL o MariaDB.
- Navegador web.
- XAMPP o un entorno equivalente.

## Instalación

### 1. Copiar el proyecto

Copiar la carpeta agenda_contactos dentro del directorio
htdocs de XAMPP.

Ejemplo en Windows:

C:\xampp\htdocs\agenda_contactos

### 2. Iniciar los servicios

Abrir el panel de control de XAMPP e iniciar:

- Apache
- MySQL

### 3. Configurar la base de datos

Abrir phpMyAdmin:

http://localhost/phpmyadmin/

Crear una base de datos llamada agenda_contactos si todavía
no existe.

Importar el archivo agenda_contactos.sql incluido en el proyecto.

### 4. Configurar la conexión

Dentro de la carpeta config, copiar el archivo:

conexion.ejemplo.php

Renombrar la copia como:

conexion.php

Editar conexion.php y colocar los datos de conexión
correspondientes al servidor MySQL o MariaDB utilizado.

No es necesario modificar el archivo original de ejemplo.

### 5. Ejecutar la aplicación

Abrir el navegador e ingresar a:

http://localhost/agenda_contactos/

Registrar una cuenta para comenzar a utilizar la agenda.

## Seguridad

La aplicación utiliza sesiones PHP para identificar a los usuarios.

Las contraseñas se almacenan mediante hash y no como texto plano.

Las operaciones sobre contactos comprueban el identificador
del usuario autenticado para impedir el acceso a contactos ajenos.

Se utilizan consultas preparadas y tokens CSRF para reforzar
la protección de las operaciones.

## Estructura principal

- index.php: panel principal y listado de contactos.
- registro.php: registro de usuarios.
- login.php: inicio de sesión.
- logout.php: cierre de sesión.
- nuevo_contacto.php: registro de contactos.
- editar_contacto.php: edición de contactos.
- eliminar_contacto.php: eliminación de contactos.
- agenda_contactos.sql: estructura de la base de datos.
- config/conexion.ejemplo.php: plantilla de conexión.

## Consideraciones

El proyecto está desarrollado para fines académicos y se ha
probado en un entorno local con XAMPP.

El archivo config/conexion.php está excluido del repositorio
para evitar publicar información de configuración privada.
