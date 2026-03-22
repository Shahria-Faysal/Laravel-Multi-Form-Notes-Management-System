# 📝 Laravel Multi-Form Notes Management System

A full-stack Notes Management System built with Laravel, featuring bulk form submission, queue-based processing, Yajra DataTables, email notifications, soft deletes, role-based access, and a complete **automated database backup system** with instant, scheduled, and continuous backup modes.

---

## 🚀 Features

### Notes Management
- ✅ Submit multiple notes at once (Bulk Form Submission)
- ✅ AJAX-based form submission via Fetch API (no page reload)
- ✅ Queue-based note storage using Laravel Jobs
- ✅ Email notification per note using Laravel Notifications
- ✅ Yajra DataTables with server-side processing
- ✅ Per-column search filters on DataTables
- ✅ Export to Excel, CSV, PDF, Print via DataTables buttons
- ✅ Soft delete with trash table
- ✅ Restore and force delete from trash
- ✅ Inline edit and delete via AJAX
- ✅ Admin sees all notes, regular users see only their own

### Backup System
- ✅ **Instant backup** — one click, runs immediately
- ✅ **Scheduled backup** — set specific time + days of the week
- ✅ **Continuous backup** — runs automatically every X minutes
- ✅ Admin gets a full database dump via `mysqldump`
- ✅ Regular users get only their own notes backed up
- ✅ Backup cooldown system to prevent spam
- ✅ Per-user backup files stored in separate folders
- ✅ Full backup log history in `backup_logs` table
- ✅ Separate queue channels (`continuous`, `scheduled`) to prevent blocking
- ✅ Enable/disable individual backup schedules via toggle
- ✅ Manual terminal backup via custom Artisan command

### Auth System
- ✅ Register, login, logout
- ✅ Admin registration via secret code (`ADMIN_SECRET_CODE` in `.env`)
- ✅ Role-based access (`is_admin` column on users)
- ✅ Auth middleware protecting backup and dashboard routes

---

## 🛠 Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel (PHP) |
| Frontend | Bootstrap 5 |
| AJAX | Fetch API |
| Database | MySQL / MariaDB |
| Queue System | Laravel Jobs |
| Notification System | Laravel Notifications |
| Table Rendering | Yajra DataTables + jQuery DataTables |
| Task Scheduling | Laravel Scheduler (`schedule:work`) |
| Backup Engine | `mysqldump` via `shell_exec()` |

---

## 🧠 Architecture Overview

### Notes Flow
```
User submits multiple forms via AJAX
→ SendEmailJob dispatched with form data + user
→ Queue worker picks it up
→ Loops through forms, creates Note for each
→ Sends Laravel Notification email per note
→ DataTables refreshes automatically
```

### Backup Flow
```
Instant backup
→ User clicks button
→ BackupScheduleController::instant()
→ Admin:  BackupService::runBackup()      → full DB dump
→ User:   BackupService::runUserBackup()  → their notes only
→ File saved → backup_logs row inserted

Scheduled backup (specific time + days)
→ User adds a slot via UI
→ Saved to user_backup_schedule table (is_continuous = 0)
→ schedule:work fires every minute
→ Checks time + day match against Carbon::now()
→ Dispatches RunScheduledBackup → queue: scheduled
→ queue:work --queue=scheduled processes it
→ Admin: runBackup() | User: runUserBackup()
→ File saved → log inserted

Continuous backup (every X minutes)
→ User sets interval (e.g. 5 minutes) via UI
→ Saved to user_backup_schedule (is_continuous = 5)
→ schedule:work fires every minute
→ Checks if X minutes passed since last backup in backup_logs
→ Dispatches RunScheduledBackup → queue: continuous
→ queue:work --queue=continuous processes it
→ File saved → log inserted
```

---

## 🗄 Database Tables

| Table | Purpose |
|---|---|
| `users` | Auth users with `is_admin` flag |
| `notes` | User notes with soft deletes |
| `user_backup_schedule` | User-defined backup schedules |
| `backup_logs` | Full history of every backup run |
| `jobs` | Laravel queue jobs (default) |
| `failed_jobs` | Failed job tracking |

### `user_backup_schedule` columns
| Column | Type | Description |
|---|---|---|
| `user_id` | foreignId | Owner of the schedule |
| `label` | string | Optional name for the slot |
| `time` | time | Time to run (scheduled mode) |
| `days` | json | Days of week e.g. `["Mo","We","Fr"]` |
| `status` | boolean | Enabled / disabled toggle |
| `is_continuous` | integer | `0` = scheduled mode, `>0` = interval in minutes |

### `backup_logs` columns
| Column | Type | Description |
|---|---|---|
| `filename` | string | Name of the `.sql` file created |
| `status` | string | `success` or `failed` |
| `interval` | integer | Interval used for cooldown |
| `user_id` | integer | Which user's backup |
| `label` | string | Schedule label |
| `is_instant` | boolean | Was it an instant backup? |

---

## 🔥 Core Classes

### `BackupService`
```php
// Full database dump — admin only
BackupService::runBackup(interval: 0, is_instant: false);

// Single user's notes only
BackupService::runUserBackup(userId: 1, label: '', isInstant: false, interval: 0);
```

### `RunScheduledBackup` (Job)
Checks if schedule owner is admin → calls `runBackup()` or `runUserBackup()` accordingly. Never uses `auth()` — always reads role from DB so it works inside queue workers.

### `SendEmailJob` (Job)
Loops through submitted forms, creates a `Note` for each, sends a `NewNote` notification email per note via `$user->notify()`.

### `NotesDataTable`
Yajra DataTable with:
- Server-side processing via AJAX
- Dual table support (notes + trash) using `tableId`
- Per-column search filter row injected via `initComplete`
- Admin sees all rows, users see only their own (scoped in `query()`)
- Export buttons: Excel, CSV, PDF, Print

### `AutoBackup` (Artisan Command)
```bash
php artisan TakeBackup:start {interval=5} {userId=0}
```
Runs an infinite loop backup in the terminal. `userId=0` = admin full backup, any other ID = user-specific backup. Useful for manual or emergency backups without touching the UI.

---

## 📂 Project Structure

```
app/
├── Console/Commands/
│   └── AutoBackup.php
├── DataTables/
│   └── NotesDataTable.php
├── Http/Controllers/
│   ├── NoteController.php
│   ├── BackupScheduleController.php
│   └── UserController.php
├── Jobs/
│   ├── SendEmailJob.php
│   └── RunScheduledBackup.php
├── Models/
│   ├── Note.php
│   ├── BackupSchedule.php
│   └── User.php
├── Notifications/
│   └── NewNote.php
├── Services/
│   └── BackupService.php
routes/
├── web.php
├── console.php
resources/views/
```

---

## 📦 Installation

### 1️⃣ Clone Repository
```bash
git clone https://github.com/Shahria-Faysal/Laravel-Multi-Form-Notes-Management-System.git
cd Laravel-Multi-Form-Notes-Management-System
```

### 2️⃣ Install Dependencies
```bash
composer install
npm install
npm run dev
```

### 3️⃣ Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

---

## 🗄 Database Setup

Update `.env`:
```env
DB_DATABASE=your_database
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
```

Run migrations:
```bash
php artisan migrate
```

---

## 🔐 Admin Setup

Add this to your `.env`:
```env
ADMIN_SECRET_CODE=your_secret_code
```

Use this code in the registration form to get admin access. Admin accounts get full DB dumps on backup and see all users' notes in DataTables.

---

## 🔧 Backup Configuration

Update the `mysqldump` path in `BackupService.php` to match your local setup:

**Windows (XAMPP):**
```php
$mysqldump = 'D:\\PROGRAMMING\\Databse\\Xampp\\mysql\\bin\\mysqldump.exe';
```

**Linux/Mac:**
```php
$mysqldump = '/usr/bin/mysqldump';
```

Backup files are saved to:
```
storage/app/backups/                     ← full DB dumps (admin)
storage/app/backups/users/{userId}/      ← per-user note backups
```

---

## 📧 Mail Configuration (Gmail Example)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="Notes App"
```

---

## ⚙ Queue Setup

```bash
# process scheduled backups
php artisan queue:work --queue=scheduled

# process continuous backups
php artisan queue:work --queue=continuous

# process emails and notifications
php artisan queue:work

# check failed jobs
php artisan queue:failed

# retry failed jobs
php artisan queue:retry all
```

---

## ⏰ Scheduler Setup

For local development:
```bash
php artisan schedule:work
```

For production, add to crontab:
```bash
* * * * * php /path-to-project/artisan schedule:run >> /dev/null 2>&1
```

---

## ▶ Run Application

You need **5 terminals** running simultaneously for full functionality:

```bash
# Terminal 1 — web server
php artisan serve

# Terminal 2 — scheduler (checks backup schedules every minute)
php artisan schedule:work

# Terminal 3 — scheduled backup queue worker
php artisan queue:work --queue=scheduled

# Terminal 4 — continuous backup queue worker
php artisan queue:work --queue=continuous

# Terminal 5 — default queue (emails, notifications)
php artisan queue:work
```

Visit: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 🎯 Concepts Demonstrated

- Laravel Queue Jobs & Multiple Queue Workers
- Laravel Task Scheduling with `schedule:work`
- Bulk Data Processing with loop-based job handling
- Laravel Notification System (per-user email)
- AJAX Form Handling with Fetch API
- Yajra DataTables server-side processing
- Dual DataTable support (active + trash) in one view
- Per-column search filter injection via DataTables `initComplete`
- Soft Delete, Restore, Force Delete
- Role-based access control (`is_admin`)
- Admin secret code registration pattern
- Background `mysqldump` execution via `shell_exec()`
- Per-user scoped database backups with `--where` flag
- Separate queue channels for performance isolation
- Backup cooldown system using `backup_logs` self-reference
- Queue worker role detection without `auth()` (DB lookup)
- Custom Artisan command with infinite loop for manual backups

---

## 👨‍💻 Author

**Fardin FW**
Backend Developer

---

## 📄 License

This project is open-source and available under the MIT License.
