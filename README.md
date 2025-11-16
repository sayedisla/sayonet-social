# AuthBoard - Mini PHP Auth Project

PHP version: 8.0+ (tested with PHP 8)
A small teaching project demonstrating:
- Registration & Login
- Sessions
- Simple Router
- Namespaces & PSR-4 autoloading (composer)
- Password hashing (password_hash)
- Email sending (Mailtrap + PHPMailer)
- Basic folder structure and simple styling

## Requirements
- PHP 8.0+
- Composer (for dependencies)
- MySQL (or MariaDB)
- Local webserver (XAMPP, Laragon, etc.)

## Setup
1. Unzip the project into your web root (or point your vhost to `AuthBoard/public`).
2. Copy `.env.example` to `.env` and fill values (DB and Mailtrap credentials).
3. Create the database and import `sql/schema.sql`.
   Example:
   ```sql
   CREATE DATABASE authboard;
   USE authboard;
   -- then import the schema.sql file
   ```
4. Install composer dependencies:
   ```bash
   composer install
   ```
5. Start the server (if using built-in PHP server for testing):
   ```bash
   cd public
   php -S localhost:8000
   ```
6. Visit `http://localhost:8000` (or your configured vhost).

## Notes for instructors
- Students should update `.env` with their Mailtrap sandbox credentials.
- The project uses a tiny .env loader (no external dotenv package required).
- Encourage students to read files under `app/` to understand flow.

