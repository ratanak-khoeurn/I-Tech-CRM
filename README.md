# I-TECH NEXUS — Student CRM & Alumni Relationship Management System

A complete CRM for an education center to manage students, alumni, communication history, follow-ups, events, opportunities, documents, reports and system settings. Built with Laravel 12 (server-rendered Blade + Bootstrap 5), MySQL, and Chart.js.

## Tech Stack

- **Backend:** Laravel 12 (PHP >= 8.2), MySQL / MariaDB
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5.3, Bootstrap Icons, Chart.js (CDN)
- **Auth:** Session-based login with role-based access control (RBAC)

## Requirements

- PHP >= 8.2 with `pdo_mysql`, `gd`/`fileinfo`, `zip` extensions
- Composer
- MySQL 5.7+ / MariaDB 10.4+
- (Optional) `mysqldump` on PATH — enables the **Download Database Backup** button in Settings

## Installation

```bash
# 1. Install PHP dependencies
composer install

# 2. Create the environment file and generate the app key
cp .env.example .env
php artisan key:generate

# 3. Configure the database in .env
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_PORT=3306
#    DB_DATABASE=student_crm
#    DB_USERNAME=root
#    DB_PASSWORD=
#    (create the database first:  CREATE DATABASE student_crm CHARACTER SET utf8mb4;)

# 4. Run migrations and seed roles, settings, courses, campuses and demo data
php artisan migrate --seed

# 5. Link the public storage disk (student photos, documents, uploads)
php artisan storage:link

# 6. Start the development server
php artisan serve
```

Open http://127.0.0.1:8000 and log in with one of the demo accounts below.

> If port 8000 is already taken by another process, use e.g. `php artisan serve --port=8123`.

## Demo Accounts

Password for all accounts: `password`

| Email                 | Role        | Capabilities |
|-----------------------|-------------|--------------|
| superadmin@itechmediakh.com | Super Admin | Everything, incl. backup download and audit logs |
| k.ratanak@itechmediakh.com  | Admin       | Manage students, staff, communication, reports, settings |
| staff@crm.test        | Staff       | View and update **assigned** students and their communication history |
| staff2@crm.test       | Staff       | Second staff account (different assignment set) |
| viewer@crm.test       | Viewer      | Read-only; confidential fields hidden |

## Roles & Permissions

- **Super Admin** – full access: all modules, settings, staff accounts, database backup, audit logs.
- **Admin** – manage students, staff, communication, reports and settings (no backup / audit logs).
- **Staff** – may view and update only the students assigned to them (`students.assigned_staff_id`), including those students' notes, documents, follow-ups and communication logs. Staff cannot open settings, staff accounts or audit logs.
- **Viewer** – read-only. Confidential fields (national ID, salary, etc.) and sensitive documents are hidden.

Sensitive documents and confidential profile fields are restricted to Admin and above regardless of assignment.

## Modules

1. **Dashboard** – KPI cards and Chart.js charts (graduation year, course, employment, engagement).
2. **Students** – full profile (sections A–G), 9-tab profile page, CRUD, archive/restore, search/filter/sort/pagination, CSV import & export, duplicate detection & merge, bulk staff assignment, profile completion indicator.
3. **Communications** – 8 channels (phone, Telegram, email, SMS, Facebook, in-person, alumni event, other), attachments, follow-up chaining. Students with `do_not_contact` status are blocked from new contact logs.
4. **Follow-ups** – priorities, statuses, overdue/today/this-week buckets, calendar view, reschedule chain, reminders.
5. **Events** – alumni events with registrations, attendance tracking and feedback.
6. **Opportunities** – jobs/internships/scholarships/trainings/volunteer roles with skill & course matching and applications.
7. **Reports** – 11 report types, exportable to CSV / Excel / PDF.
8. **Documents** – per-student document library with sensitive-document restriction and secure download.
9. **Notifications** – in-system notification center (generated from follow-ups, events, opportunities).
10. **Settings** – center info & logo, notification preferences, backup settings, campuses, courses, staff accounts, audit logs.

## Important Behaviour Notes

- **No external messages are sent automatically.** Telegram / Email / SMS / WhatsApp integration is *not* wired up; communication records are manual logs only. The Settings page states this explicitly. Do not claim outbound messaging works unless a real provider is configured and connected.
- **Do Not Contact** students cannot receive new communication logs; the UI and controller both enforce this.
- **Audit logs** record create/update/delete/status/download actions with user, IP and description.
- All mutating forms use `POST` with `_method` spoofing (`PUT`/`PATCH`/`DELETE`), CSRF-protected.
- Uploads are validated by MIME and size and stored on the `public` disk outside the web root's executable path.

## Useful Commands

```bash
php artisan migrate:fresh --seed   # reset DB and reseed demo data
php artisan storage:link           # expose uploaded files
php artisan view:cache             # precompile Blade views (optional)
php artisan route:list             # inspect all routes
```

## Attendance & Biometrics

Attendance is recorded as a daily check-in / check-out per person (student or
employee). Three ways feed the same record:

- **Manual / ID scan** — Attendance page → enter or scan a `student_code` /
  `employee_code`. First scan checks in, next checks out.
- **Face scan** — webcam capture matched against enrolled face embeddings. Needs
  the Python face microservice running; see `python-face/README.md`. Enroll a
  face from a student's or employee's profile page (with consent).
- **Fingerprint / device import** — for standalone terminals (ZKTeco, Futronic,
  DigitalPersona, …).

### Fingerprint terminals

The terminal performs enrollment **and** matching on its own hardware — the CRM
never touches raw fingerprints or templates. It only imports the device's punch
logs and turns them into check-in / check-out records.

To use it:

1. On the device, enroll each person and set their **User ID / PIN equal to
   their CRM code** (`student_code` or `employee_code`). If a code could match
   both a student and an employee, add a `person_type` column (`student` /
   `employee`) to disambiguate.
2. Export the punch log as CSV from the device software. The importer accepts a
   `user_id` (or `code`/`pin`/`employee_code`) column plus either one
   `date`+`time` pair or a single `datetime`/`timestamp` column. Download the
   sample from **Attendance → Fingerprint / device punch import → Sample
   template**.
3. Upload that CSV on the same card. The result summary reports checked-in,
   checked-out, not-found, ambiguous and invalid rows.

> **Automated pull (not yet wired):** to sync without manual CSV export, add a
> scheduled job that pulls logs over the ZKTeco ZK protocol (e.g. the `pyzk`
> Python library) and feeds the same `AttendanceRecorder` service. This requires
> the device to be reachable on the CRM's network (IP + port + credentials).

## Troubleshooting

- **Backup button 404s / fails:** install MySQL client tools so `mysqldump` is on PATH, or the button will report that backups are unavailable.
- **Uploads not visible:** run `php artisan storage:link`.
- **Port already in use:** pass `--port=XXXX` to `php artisan serve`.
