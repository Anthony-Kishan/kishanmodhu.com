# kishanmodhu.com

Personal portfolio site with a custom PHP + MySQL content management system.

The public site is unchanged visually — every heading, image, animation and
section is the same as before. What changed is where the content lives: it now
comes from a database and is editable through an admin dashboard at `/admin`
rather than by editing HTML.

---

## Requirements

- PHP 8.1 or newer with `pdo_mysql`, `mbstring`, `fileinfo` and `gd`
- MySQL 5.7+ / MariaDB 10.3+
- Apache with `mod_rewrite` (an nginx config is given below)

No Composer packages and no JavaScript build step. `composer.json` does not
exist because the application has no third-party dependencies.

---

## Installation

```bash
# 1. Configuration
cp .env.example .env
#    Fill in DB_* and set APP_URL. Keep APP_DEBUG=false outside development.

# 2. Database
mysql -u USER -p -e "CREATE DATABASE kishanmodhu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u USER -p kishanmodhu < database/schema.sql

# 3. Seed the site's existing content
php database/seed.php

# 4. Create your login
php database/create_admin.php "Kishan Modhu" you@example.com

# 5. Writable directories
chmod -R 755 storage/logs public/assets/uploads
```

Then sign in at `https://your-domain/admin`.

`seed.php` only inserts into tables that are empty, so re-running it will never
overwrite content you have edited. Use `php database/seed.php --fresh` to wipe
the content tables and start over (admin accounts and contact messages are left
alone).

### Local development

```bash
php -S localhost:8000 -t public public/index.php
```

---

## Deployment

**Point the web server's document root at `public/`.** Everything else —
`app/`, `config/`, `database/`, `storage/` and `.env` — must stay outside the
web root.

If your host will not let you move the document root (typical on shared cPanel
plans), the `.htaccess` in the project root rewrites into `public/` and blocks
direct access to the application directories. Delete that file if you *can* set
the document root properly; it is the weaker of the two arrangements.

<details>
<summary>nginx server block</summary>

```nginx
server {
    root /var/www/kishanmodhu.com/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Uploads are data, never code.
    location ^~ /assets/uploads/ {
        location ~ \.php$ { deny all; }
    }
}
```
</details>

> **Note on hosting.** The site previously ran on GitHub Pages and Vercel as
> static files. Neither serves PHP, so both links are now out of date — this
> needs PHP hosting. The old `/index.html` and `/contact.html` URLs redirect
> permanently to `/` and `/contact`, so existing inbound links keep working.

---

## Deploying to Hostinger with GitHub Actions

[`.github/workflows/deploy.yml`](.github/workflows/deploy.yml) lints, tests and
then publishes over FTPS on every push to `main`. A failing check blocks the
upload.

### 1. Choose a layout on the server

**Option A — document root moved to `public/` (preferred).**
In hPanel → Websites → your domain → Advanced → set the document root to
`public_html/public`. Deploy the whole project into `public_html/`, so
`app/`, `config/`, `database/` and `.env` sit outside what the web server
serves. Set the repository variable:

```
FTP_REMOTE_DIR = public_html/
```

**Option B — everything inside the web root.**
If your plan won't let you move the document root, deploy to `public_html/`
anyway and rely on the root [`.htaccess`](.htaccess), which rewrites into
`public/` and returns 403 for `app|config|database|storage` and `.env`. This
works, but the application files are only protected by Apache rules rather than
being unreachable outright. Same variable value as above.

Either way, **use option A if hPanel offers it.**

### 2. Create a deployment FTP account

hPanel → Files → FTP Accounts → create one scoped to the site directory. Note
the host (`ftp.your-domain.com`), username and password.

### 3. Add the repository secrets and variables

GitHub → Settings → Secrets and variables → Actions.

| Kind | Name | Value |
|---|---|---|
| Secret | `FTP_SERVER` | `ftp.your-domain.com` |
| Secret | `FTP_USERNAME` | `u123456789.deploy` |
| Secret | `FTP_PASSWORD` | the FTP password |
| Variable | `FTP_REMOTE_DIR` | `public_html/` (trailing slash required) |
| Variable | `HEALTHCHECK_URL` | `https://your-domain.com/` (optional) |

### 4. One-time server setup

The workflow deliberately never touches these, so do them once by hand.

**Create `.env` on the server** (hPanel → File Manager, next to `app/`). Use
your Hostinger MySQL credentials from hPanel → Databases → Management:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_TIMEZONE=Asia/Dhaka

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456789_kishanmodhu
DB_USERNAME=u123456789_kishan
DB_PASSWORD=your-db-password
```

> `APP_DEBUG` **must** be `false` in production — with it on, exceptions print
> stack traces to visitors. On Hostinger use `DB_HOST=localhost` (its MySQL is
> reached over a socket), unlike the `127.0.0.1` used locally.

**Import the schema** via hPanel → Databases → phpMyAdmin → Import →
`database/schema.sql`.

**Seed and create your login.** `database/` is not deployed, so either run these
over SSH from a temporary copy (Business plans and above), or seed locally
against a matching database and export. With SSH:

```bash
php database/seed.php
php database/create_admin.php "Kishan Modhu" you@example.com
```

**Set the PHP version** to 8.2 in hPanel → Advanced → PHP Configuration, and
enable `pdo_mysql`, `mbstring`, `fileinfo` and `gd`. If you pick a different
version, change `php-version` in the workflow to match.

### 5. Deploy

```bash
git push origin main
```

Watch it under the repository's Actions tab. The first run uploads everything
and is slow; later runs transfer only what changed.

### What is never deployed

`.env` · `database/` · `tests/` · `.github/` · `*.md` · `storage/logs/*.log` ·
`public/assets/uploads/**`

Uploads are excluded so a deploy can never delete media added through the CMS.
The uploads directory is created automatically on first upload, and PHP
execution inside it is blocked by a rule in
[`public/.htaccess`](public/.htaccess).

### Troubleshooting the deploy

The `deploy` job runs two preflight steps before uploading anything, so most
misconfiguration is reported in plain language rather than as an FTP error.

**`FTPError: 530 Login incorrect`** — the server rejected the username and
password. In order of likelihood:

1. **Wrong account type.** `FTP_USERNAME` and `FTP_PASSWORD` must come from
   hPanel → Files → **FTP Accounts**, not your Hostinger account login. The
   username looks like `u123456789` or `u123456789.deploy`, never an email
   address.
2. **Whitespace in a secret.** A trailing space or newline picked up while
   copying is invisible in the GitHub secrets UI. The preflight now catches
   this — re-paste the value and save.
3. **Password never set.** A freshly created FTP account may have no usable
   password until you set one explicitly. Use *Change FTP password* in hPanel,
   then update the secret.
4. **Wrong host.** `FTP_SERVER` must be the hostname or IP from the FTP
   Accounts page. If your domain does not yet point at Hostinger,
   `ftp.your-domain.com` resolves somewhere else entirely — use the IP instead.
5. **Propagation.** A newly created FTP account can take a few minutes to work.

**`FTPS` fails but plain FTP works** — the preflight says so explicitly. Set the
repository variable `FTP_PROTOCOL` to `ftp`. Prefer fixing FTPS if you can:
plain FTP sends the password unencrypted.

**Deploy succeeds but the site 500s** — check `storage/logs/php-error.log` on
the server. Usually a missing or wrong `.env`, or a schema change that has not
been applied yet.

**Deploy succeeds but nothing changed** — the action keeps
`.ftp-deploy-sync-state.json` on the server to track what it has already sent.
Delete it to force a full re-upload.

### Schema changes

There is no migration runner. If you add a table or column, apply it to the
production database through phpMyAdmin **before** pushing the code that needs
it, or requests will fail with a missing-column error.

---

## Architecture

```
public/            ← document root; the only publicly reachable directory
  index.php          front controller
  assets/            css, js, images, uploads
app/
  Core/              framework: Router, Model, View, Auth, Validator, …
  Models/            one class per table
  Controllers/
    Site/            public pages
    Admin/           dashboard
  Middleware/        RequireAuth, RequireAdmin, VerifyCsrf
  Services/          SiteContent, MediaLibrary
  Views/
    layouts/         site, admin, auth, minimal
    partials/        header, footer, flash, field
    site/sections/   one file per page section
    admin/           dashboard screens
config/
  routes.php         route table
  content_types.php  ← the CMS registry (see below)
  settings.php       ← the settings screens
database/
  schema.sql, seed.php, create_admin.php
storage/logs/
```

Request flow: `public/index.php` → `app/bootstrap.php` (autoload, env, session,
error handling) → `Router` → middleware → controller → `Response`.

### Adding a new content type

Content types are data, not code. To add one — "Blog posts", say:

1. **Add a table** in `database/schema.sql`. Include `sort_order` and
   `is_published` if you want drag-to-reorder and a visibility toggle.
2. **Add a model** in `app/Models/` extending `App\Core\Model`, listing the
   writable columns in `$fillable`.
3. **Add an entry** to `config/content_types.php`.

The list screen, create/edit forms, validation, reordering, publish toggle,
routes and sidebar link are all generated from that registry entry. No new
controller, route or view is needed.

Field types available: `text`, `textarea`, `number`, `url`, `email`, `select`,
`boolean`, `image`, `list` (repeatable strings stored as JSON).

Validation rules use the vocabulary in `App\Core\Validator`: `required`, `min`,
`max`, `email`, `url`, `integer`, `numeric`, `between`, `in`, `confirmed`.

### Adding a setting

Add a field to any group in `config/settings.php`. It becomes editable
immediately and is readable in any view via `$settings->get('your_key')`.

---

## The admin dashboard

| Screen | What it manages |
|---|---|
| Dashboard | Record counts, unread message badge |
| Works, Services, Testimonials, Experience, Stack, Marquee logos, Certificates, Social links | Full CRUD, drag-to-reorder, show/hide |
| Settings | SEO and sharing tags, hero, section headings, about, contact — grouped into tabs |
| Media | Upload images; browse the bundled theme images |
| Messages | Contact-form inbox with read/unread state |
| Users | Admin accounts (visible to the `admin` role only) |

**Roles.** `admin` can do everything including user management. `editor` can
manage content and settings but not other accounts.

### Security

- Passwords hashed with `password_hash()` (bcrypt), rehashed on login when the
  cost factor changes
- Login throttling: 5 failed attempts triggers a 15-minute lockout
- Session ID regenerated on login; `HttpOnly`, `SameSite=Lax`, `Secure` over HTTPS
- Idle sessions expire (`SESSION_IDLE_TIMEOUT`, default 2 hours)
- CSRF token required on every state-changing request, compared with `hash_equals`
- All queries use prepared statements; column names are whitelisted against
  each model's `$fillable`
- All template output escaped through `e()`
- Uploads restricted by sniffed MIME type, not filename; SVGs scanned for
  scripting; the uploads directory has PHP execution disabled
- Contact form protected by CSRF, a honeypot field, and a per-IP rate limit

---

## Contact form

Submissions now post to this application instead of the previous third-party
endpoint. Each one is validated, rate-limited (5 per hour per IP), stored, and
shown in **Admin → Messages**.

To also receive an email per submission, set a **Notification email** under
**Settings → Contact**. It uses PHP's `mail()`; on hosts without a working MTA,
point it at an SMTP relay instead. Delivery failure never blocks a submission —
the message is already saved.
