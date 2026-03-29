# OM Diagnostic Lab

PHP + MySQL diagnostic lab web application with public booking flow and admin management panel.

## Project Structure

- `index.php`, `about.php`, `contact.php`, `health-package.php`: public pages
- `login.php`, `register.php`, `logout.php`: customer auth flow
- `booking-success.php`, `test-results.php`: booking/result pages
- `includes/`: shared header and footer
- `assets/css/`, `assets/js/`, `assets/image/`: static assets
- `admin/`: admin authentication, dashboard, appointments, reports, users
- `src/`: database connection, constants, SQL seed/schema files

## Requirements

- PHP 8.0+
- MySQL / MariaDB
- Apache (or any PHP-compatible web server)

## Setup

1. Clone/copy project into your web root (for example `htdocs/diagonistic-lab`).
2. Create database:
   - Name: `jobportal`
3. Import SQL:
   - File: `src/sql/appointment.sql`
4. Update DB config if needed:
   - File: `src/constants/server.php`
   - Default values are:
     - host: `localhost`
     - user: `root`
     - password: ``
     - database: `jobportal`
5. Ensure upload directory exists and is writable:
   - `uploads/` (create it at project root if missing)

## Run

If using XAMPP/WAMP/MAMP:

1. Start Apache and MySQL.
2. Open:
   - `http://localhost/diagonistic-lab/`

## Admin Access

- Admin login page: `admin/login.php`
- Manage users, appointments, reports, and package-related workflows from the admin panel.

## SMTP / Email (Admin Appointment Notifications)

Admin email sending uses PHPMailer and now expects environment variables:

- `LAB_SMTP_USERNAME` (optional; defaults to existing sender address)
- `LAB_SMTP_PASSWORD` (required)

Example (macOS/Linux shell before starting PHP server):

```bash
export LAB_SMTP_USERNAME="your-email@gmail.com"
export LAB_SMTP_PASSWORD="your-app-password"
```

If `LAB_SMTP_PASSWORD` is missing, the app will skip sending and return a clear error.

## Notes

- Health package page reads package catalog from database table `diagnostic_packages`.
- If no package rows exist, users will see: `There is no any test report right now.`
- Shared includes are standardized with `include_once`/`require_once` for safer loading.
