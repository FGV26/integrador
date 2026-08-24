# Levantar el proyecto con Docker

Este proyecto es una aplicacion PHP clasica que antes dependia de XAMPP, MySQL y phpMyAdmin. Con esta configuracion se levanta con:

- PHP 8.2 + Apache
- MySQL 8.0
- phpMyAdmin
- MinIO para almacenar imagenes subidas por usuarios

## Requisitos

- Docker Desktop instalado y abierto.

## Primer arranque

Desde la carpeta del proyecto:

```powershell
docker compose up --build
```

Luego abre:

- Aplicacion: http://localhost:8080/
- phpMyAdmin: http://localhost:8081/
- MinIO API: http://localhost:9000/
- MinIO Console: http://localhost:9001/

## Credenciales de phpMyAdmin

- Servidor: `db`
- Usuario: `root`
- Contrasena: `root_pass`

Tambien existe el usuario MySQL de la app:

- Usuario: `integrador`
- Contrasena: `integrador_pass`
- Base de datos: `estudiojuridico`

## Usuarios de prueba de la aplicacion

Todos usan la contrasena `password`.

- Administrador: `admin`
- Abogado: `abogado`
- Cliente: `cliente`

## Credenciales de MinIO

- Usuario: `integrador_minio`
- Contrasena: `integrador_minio_pass`
- Bucket: `integrador-images`

La aplicacion guarda las imagenes subidas en MinIO y conserva en MySQL la key del objeto, por ejemplo:

```text
users/2026/06/archivo.jpg
```

## Reiniciar la base de datos

Si cambias el SQL inicial o quieres empezar limpio:

```powershell
docker compose down -v
docker compose up --build
```

El archivo que crea las tablas esta en:

```text
docker/mysql/init/01-schema.sql
```

## Notas

- La app corre en el contenedor `app`, pero se conecta a MySQL usando el host interno `db`.
- La conexion PHP lee variables de entorno en `conexiones/conexion.php`.
- Las URLs base se calculan en `config/app.php`, por eso ya no dependen de `http://localhost/INTEGRADOR/`.
- Las subidas de imagenes usan `config/storage.php`, con API compatible con S3 para facilitar la futura migracion a Laravel.
