# JobSpot
Proyecto de fin de curso de DAW — Elías Bagüeste Amate.

## Descripción
JobSpot es una bolsa de empleo local que conecta candidatos y empresas, y permite a un administrador gestionar y moderar la plataforma.

## Stack
- PHP (procedimental por ahora)
- Apache
- HTML
- CSS
- JavaScript (solo cuando aporta valor real)
- SQL
- MariaDB
- PDO (PHP Data Objects)

## Entornos
- Local: `http://jobspot.local`
- Desarrollo: `https://dev.jobspot.es`
- Producción: `https://jobspot.es`

## Reglas técnicas críticas
- `BASE_URL` debe permanecer en `''`.
- No usar rutas hardcodeadas con subcarpeta (`/jobspot/...`).
- La aplicación debe colgar de la raíz del host.
- `config/env.php` **no** se sube al repositorio.
- `config/env.example.php` **sí** se sube al repositorio.
- `config/database.php` es único para todos los entornos.

## Puesta en marcha en local (XAMPP)

### 1) Montar el proyecto en XAMPP
- Copia la carpeta del proyecto en:
  `C:\xampp\htdocs\jobspot`
- Enciende **Apache** y **MySQL/MariaDB** desde el Panel de Control de XAMPP.
- Accede desde el navegador a `http://jobspot.local`

### 2) Usar siempre `jobspot.local`
En Windows añade esta línea al archivo `hosts`:
```
127.0.0.1 jobspot.local
```
Y crea un VirtualHost en Apache apuntando a `C:\xampp\htdocs\jobspot`.

### 3) Crear la configuración local
- Copia `config/env.example.php` y pégalo como `config/env.php`
- Rellena tus datos de conexión a la BD:
  - `DB_HOST` → normalmente `localhost`
  - `DB_NAME` → `jobspot`
  - `DB_USER` → normalmente `root`
  - `DB_PASS` → normalmente vacío `''`

### 4) Crear e importar la base de datos
- Abre `http://localhost/phpmyadmin`
- Crea una base de datos llamada `jobspot`
- Importa en este orden:
  1. `db/schema.sql` → crea las tablas
  2. `db/seed.sql` → inserta usuarios de prueba

## Ramas
- `main` → versión estable (producción)
- `develop` → versión en desarrollo
