# JobSpot
Proyecto de fin de curso de DAW.

## Descripción
JobSpot es una bolsa de empleo local que conecta candidatos y empresas, y permite a un administrador gestionar y moderar la plataforma.

## Stack
- PHP
- Apache
- HTML
- CSS
- JavaScript
- MariaDB

## Entornos
- Local: `http://jobspot.local`
- Desarrollo: `https://dev.jobspot.es`
- Producción: `https://jobspot.es`

## Configuración local
Cada miembro debe crear:
- `config/env.php`

a partir de:
- `config/env.example.php`

## Equipo
- Elías
- Fátima
- Sufian

## Reglas técnicas críticas del proyecto
- `BASE_URL` debe permanecer en `''`.
- No usar rutas hardcodeadas con subcarpeta (`/jobspot/...`).
- La aplicación debe colgar de la raíz del host (`jobspot.local`, `dev.jobspot.es`, `jobspot.es`).
- `config/env.php` **no** se sube al repositorio.
- `config/env.example.php` **sí** se sube al repositorio.
- `config/database.php` es único para local, desarrollo y producción.

## Puesta en marcha en local (XAMPP) + reglas de GitHub

### 1) Montar el proyecto en XAMPP (en tu PC)
- Mete la carpeta del proyecto aquí:  
  `C:\xampp\htdocs\jobspot`
- Abre el Panel de Control de XAMPP y enciende:
  - **Apache** (para servir la web)
  - **MySQL/MariaDB** (para la BD (Base de Datos))
- Entra desde el navegador a:  
  `http://jobspot.local`
- Si Apache y MySQL/MariaDB están en verde, está OK.

---

### 2) Usar SIEMPRE `jobspot.local` (no `localhost/jobspot`)
Esto es importante para que el proyecto funcione igual en:
- local (cada uno)
- dev
- prod

Qué hay que hacer:
- En Windows, en el archivo `hosts`, añadir:  
  `127.0.0.1 jobspot.local`
- En Apache, crear un **VirtualHost (host virtual)** apuntando a:  
  `C:\xampp\htdocs\jobspot`

Resumen: el proyecto debe “colgar” de la **raíz del dominio**, no dentro de una subcarpeta.

---

### 3) Crear tu configuración local (`config/env.php`)
En el repositorio existen:
- `config/env.example.php` → ejemplo (SÍ se sube a GitHub)
- `config/env.php` → tu configuración real (NO se sube)

Qué haces:
- Copias `config/env.example.php`
- Lo pegas como `config/env.php`

---

### 4) Rellenar los datos de la BD en `env.php`
En `config/env.php` pones tus datos locales:
- `DB_HOST` → normalmente `localhost`
- `DB_NAME` → nombre de tu BD (ej: `jobspot`)
- `DB_USER` → normalmente `root` (en XAMPP suele ser `root`)
- `DB_PASS` → normalmente vacío `''` (depende de tu XAMPP)

Nota: cada persona puede tener contraseña distinta. Por eso `env.php` es “personal”.

---

### 5) Crear e importar la BD (Base de Datos) en phpMyAdmin
- Abre: `http://localhost/phpmyadmin`
- Crea una BD con el mismo nombre que pusiste en `DB_NAME` (ej: `jobspot`)
- Importa estos archivos **en este orden**:
  1) `db/schema.sql` → crea las tablas (estructura)
  2) `db/seed.sql` → mete datos de prueba (ofertas, usuarios demo, etc.)

Importante: si importas `seed.sql` antes de `schema.sql`, fallará porque no existen las tablas.

---

### 6) Regla crítica de GitHub con `env.php`
- `config/env.php` **NUNCA** se sube a GitHub (va en `.gitignore`)  
  Motivo: contiene contraseñas y datos de tu PC.
- `config/env.example.php` **SÍ** se sube a GitHub  
  Motivo: es la plantilla para que cada uno cree su `env.php`.

---

### 7) Ramas (branches, ramas) — explicado fácil
- `main` → “versión final” (producción). **No se programa aquí.**
- `develop` → donde se juntan las cosas antes de pasar a `main`
- `feature/...` → rama de trabajo por tarea (cada uno hace la suya)

Ejemplos:
- `feature/home-fatima`
- `feature/auth-sufian`
- `feature/jobs-elias`

---

### 8) Flujo de trabajo en GitHub (lo que hacemos siempre)
1) Crear una rama `feature/...`
2) Hacer cambios
3) Subir esa rama a GitHub (push)
4) Abrir un **PR (Pull Request, solicitud de fusión)** hacia `develop`
5) Se revisa y se fusiona a `develop`

Regla: nadie mete cosas “a lo bruto” en `develop` o `main`.

#### Si no te aparece ningún PR en GitHub
Revisa esto en orden:
1) Has hecho `push` de tu rama `feature/...` al remoto.
2) La rama existe en GitHub y contiene commits nuevos.
3) Has creado el PR manualmente desde **New pull request** comparando `feature/...` contra `develop`.
4) Si no existe remoto `origin` en local, añádelo antes de hacer push.

Comandos típicos:
```bash
git remote -v
git push -u origin feature/tu-rama
```

---

### 9) Paso final (cuando ya está todo bien)
Cuando `develop` esté estable:
- se pasa a `main`
- y Elías hace el despliegue a:
  - `dev.jobspot.es`
  - `jobspot.es`