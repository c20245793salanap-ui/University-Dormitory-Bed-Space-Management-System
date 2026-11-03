# University Dormitory Bed Space Management

A Laravel starter application for managing dormitory buildings, rooms, bed slots, students, and lease contracts.

## Features

- Dashboard with bed occupancy, registered students, buildings, rooms, active leases, and payments recorded.
- Searchable management pages with create, edit, and delete actions for all five dormitory records.
- Dedicated Form Requests and resource controllers for each record, with a service layer for persistence, sanitization, and lease/bed-slot transactions.
- Focused resource workflows for dorm rooms and dorm contracts, with dedicated validation and controller actions.
- Foreign-key validation and relationship-aware forms.
- Unique student identifiers, building names, room numbers within each building, and bed labels within each room.
- Sample records based on the provided 3NF tables.
- User registration, session-based login/logout, and email password resets.
- Authentication protection for the dashboard and all dormitory management pages.

## Business rules

- Student IDs are trimmed, stripped of markup, normalized to uppercase, and stored without embedded whitespace (for example, `bsis-2026-001` becomes `BSIS-2026-001`).
- Building names are trimmed, stripped of markup, and title-cased (for example, `north hall` becomes `North Hall`).
- A contract may only be created for a bed slot whose status is `Vacant`. The vacancy is checked again inside a transaction when a pending contract is signed by changing its lease status to `Active`.
- Signing an active contract marks its bed slot `Occupied`. Deactivating or deleting the last active contract for that slot makes it `Vacant`.

## Database structure

- `students`: unique student number and student name.
- `dorm_buildings`: unique building name.
- `dorm_rooms`: building foreign key and room number, unique within a building.
- `bed_slots`: room foreign key, bed label, and vacancy status, unique within a room.
- `dorm_contracts`: student and bed-slot foreign keys, lease dates, rates, deposit, payment total, and lease status.

Bed-slot status is a separate 3NF attribute. Creating, activating, changing, or deleting an active contract synchronizes the affected bed slot in a database transaction; the example seed rows preserve the values supplied in the tables.

## Local setup

1. Install PHP 8.3+ with the required PDO database extension, Composer, and Node.js/npm.
2. Copy `.env.example` to `.env` if you do not already have a local environment file, then generate an application key:

   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```

3. Configure a database in `.env`. For a local SQLite database, set `DB_CONNECTION=sqlite` and `DB_DATABASE=database/database.sqlite`, then create the file if needed:

   ```powershell
   New-Item -ItemType File -Force database/database.sqlite
   ```

   The `mssql` connection name is also supported and uses Laravel's SQL Server driver; the PHP `pdo_sqlsrv` extension must be installed to connect to SQL Server.

4. Run migrations and load the example records:

   ```powershell
   php artisan migrate --seed
   ```

5. Install and build the frontend, then start the app:

   ```powershell
   npm install
   npm run build
   php artisan serve
   ```

Open the local URL printed by `php artisan serve`.

## Authentication and email

Guests can register, sign in, and request a password reset. The dashboard and dormitory management pages require an authenticated account. Configure a real mail transport in `.env` (for example `MAIL_MAILER=smtp` and the matching `MAIL_HOST`, `MAIL_PORT`, username, password, and sender address) to deliver password-reset links. With the default `MAIL_MAILER=log`, reset messages are written to the application log for local development.

## Important

This is a development starter and does not include roles or fine-grained permissions. Registration is open to anyone who can reach the app; configure access restrictions or disable public registration before exposing it to a network or using real student records.
