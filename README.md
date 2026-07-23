# JobSpot

A local job board that connects candidates and companies, with an admin role to manage and moderate the platform. Built as my final project for the CFGS (Higher Vocational Training) in Web Application Development, and deployed in production on my own self-hosted infrastructure.

Live at **[jobspot.es](https://jobspot.es)**.

## What it does

JobSpot has three user roles, each with its own dashboard:

- **Candidates** can browse and filter job listings (by category, contract type, work mode), save favourites, apply with a message, and track the status of their applications.
- **Companies** can create a company profile, publish and manage job listings, and review incoming applications.
- **Admins** can moderate listings, verify companies, and manage user accounts.

Anyone can browse and search published listings without an account; creating a profile, applying, or posting jobs requires registering as a candidate or company.

## Tech stack

- **Backend:** PHP 8, procedural style (no framework)
- **Database:** MariaDB, accessed through PDO with prepared statements
- **Frontend:** Server-rendered HTML/CSS, with JavaScript only where it adds real value (client-side form validation, favourite toggling)
- **Web server:** Apache
- **Deployment:** Docker containers behind Traefik as a reverse proxy, with automatic HTTPS (Let's Encrypt)

## Security

This app handles real personal data (uploaded CVs, contact details), so the following is implemented rather than assumed:

- **Password storage:** `password_hash()` with bcrypt — passwords are never stored or logged in plain text.
- **SQL injection protection:** every query goes through PDO prepared statements with bound parameters (`PDO::ATTR_EMULATE_PREPARES` disabled, so the driver sends real prepared statements, not PHP-side emulation).
- **XSS protection:** all dynamic output is escaped with `htmlspecialchars()` before being rendered.
- **Session fixation protection:** `session_regenerate_id(true)` is called on every successful login.
- **Access control:** every protected page checks both authentication (`requireLogin()`) and role (`requireRole()`) before rendering anything — a candidate cannot reach a company or admin page by guessing a URL.
- **Least-privilege sessions:** only non-sensitive user data (id, name, email, role) is kept in `$_SESSION`; the password hash never leaves the database layer.
- **Environment-aware error handling:** in production, database and application errors are hidden from the user and never leak internal details (query text, stack traces); in local/dev they're shown in full for debugging.
- **User enumeration mitigation:** login failures always return the same generic message ("email or password incorrect") whether the email exists or not.
- **Secrets kept out of git:** database credentials live in `config/env.php`, which is gitignored; only `config/env.example.php` (with placeholder values) is committed.

Known gaps, for transparency: there is no CSRF token protection on forms yet, and no rate-limiting on login attempts. Both are on the list of things to add.

## Database schema

Seven tables, all InnoDB with foreign keys enforcing referential integrity:

- `users` — one row per account; `role` is an enum (`candidate` / `company` / `admin`)
- `candidate_profiles` — 1:1 with `users`, extra fields for candidates (phone, city, summary, CV path)
- `companies` — 1:1 with `users`, company profile (legal name, brand name, tax ID, description, verification status)
- `categories` — job categories (Technology, Hospitality, Construction, etc.)
- `jobs` — job listings, belongs to a `company` and a `category`; `status` tracks the moderation/publication lifecycle (`draft` → `pending` → `published`/`rejected` → `closed`)
- `applications` — a candidate applying to a job, with a status (`sent` / `reviewed` / `accepted` / `rejected`)
- `favorite_jobs` — candidates bookmarking jobs

Full definitions in [`db/schema.sql`](db/schema.sql); realistic demo data in [`db/seed.sql`](db/seed.sql).

## Environments

| Environment | URL | Branch |
|---|---|---|
| Local | `http://jobspot.local` | — |
| Staging | `https://dev.jobspot.es` (HTTP basic auth) | `develop` |
| Production | `https://jobspot.es` | `main` |

The app detects which environment it's running in from the `Host` header (see `config/config.php`) and adjusts error display accordingly — full errors in local/dev, hidden in production.

## Key technical constraints

- `BASE_URL` must stay `''` — the app is designed to live at the root of its host, never in a subfolder (no hardcoded `/jobspot/...` paths anywhere).
- `config/env.php` is never committed; `config/env.example.php` is, as a template.
- `config/database.php` is shared across every environment — behaviour differs only through `env.php` values and the `APP_ENV` detection above.

## Running it locally (XAMPP)

1. **Copy the project into XAMPP's web root:**
   `C:\xampp\htdocs\jobspot`, then start **Apache** and **MySQL/MariaDB** from the XAMPP control panel.

2. **Set up the `jobspot.local` hostname.**
   Add this line to your `hosts` file:
   ```
   127.0.0.1 jobspot.local
   ```
   Then create an Apache VirtualHost pointing to `C:\xampp\htdocs\jobspot`, and access the site at `http://jobspot.local`.

3. **Create your local config.**
   Copy `config/env.example.php` to `config/env.php` and fill in your local DB connection:
   - `DB_HOST` → usually `localhost`
   - `DB_NAME` → `jobspot`
   - `DB_USER` → usually `root`
   - `DB_PASS` → usually empty (`''`)

4. **Create and import the database.**
   Open `http://localhost/phpmyadmin`, create a database called `jobspot`, then import, in this order:
   1. `db/schema.sql` — creates the tables
   2. `db/seed.sql` — inserts demo users, companies and jobs

### Demo accounts (seed data)

Password for every seeded account: `Test1234`

| Role | Email |
|---|---|
| Admin | `admin@jobspot.local` |
| Company (verified) | `atlantic@jobspot.local` |
| Company (unverified, no jobs) | `sinverificar@jobspot.local` |
| Candidate | `ana.garcia@jobspot.local` |

(Full list in the header comment of `db/seed.sql`.)

## Branches & workflow

- `main` — production, deployed straight to jobspot.es. **Protected: changes go through a Pull Request**, no direct pushes.
- `develop` — active development, deployed to the staging environment (dev.jobspot.es) for testing before a PR into `main`.
