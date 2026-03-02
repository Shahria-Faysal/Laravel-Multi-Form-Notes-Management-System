📝 Laravel Multi-Form Notes Management System

A dynamic Notes Management System built with Laravel featuring bulk form submission, queue-based data storage, AJAX integration, DataTables, and email notifications using Laravel Notifications.

🚀 Features

✅ Submit multiple notes at once (Bulk Form Submission)

✅ AJAX-based form submission (No full page reload)

✅ Queue-based data processing

✅ Email notification using Laravel Notification

✅ DataTables live rendering

✅ Failed job tracking

✅ Clean MVC architecture

✅ Bootstrap UI

🛠 Tech Stack

Backend: Laravel

Frontend: Bootstrap 5

AJAX: jQuery

Database: MySQL / MariaDB

Queue System: Laravel Jobs

Notification System: Laravel Notifications

Table Rendering: jQuery DataTables

🧠 Architecture Overview
How It Works

User submits multiple forms via AJAX.

A Job is dispatched with all form data.

Inside the Job:

Each note is stored using a loop.

After storing, a Laravel Notification is sent via email.

DataTables refreshes dynamically.

Queue worker processes everything asynchronously.

🔥 Job Logic (Core Feature)
public function handle(): void
{
    foreach ($this->request['forms'] as $noteData) {

        $note = Note::create([
            'title' => $noteData['title'],
            'note' => $noteData['note'],
        ]);
    }

    Notification::route('mail', 'fardin360360@gmail.com')
                ->notify(new NewNote($note));
}
What This Does

Loops through multiple submitted forms

Stores each note inside the queue

Sends a notification email after processing

Uses Notification::route() (No authentication required)

📦 Installation
1️⃣ Clone Repository
git clone https://github.com/your-username/your-repo-name.git
cd your-repo-name
2️⃣ Install Dependencies
composer install
npm install
npm run dev
3️⃣ Setup Environment
cp .env.example .env
php artisan key:generate
🗄 Database Setup

Update .env:

DB_DATABASE=your_database
DB_USERNAME=root
DB_PASSWORD=

Run migrations:

php artisan migrate
📧 Mail Configuration (Gmail Example)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="Notes App"
⚙ Queue Setup

Run queue worker:

php artisan queue:work

Check failed jobs:

php artisan queue:failed

Retry failed jobs:

php artisan queue:retry all
▶ Run Application
php artisan serve

Visit:

http://127.0.0.1:8000
📂 Important Files

app/Jobs/StoreNotesJob.php

app/Notifications/NewNote.php

app/Http/Controllers/NoteController.php

resources/views/notes/

routes/web.php

🎯 Concepts Demonstrated

Laravel Queue Jobs

Bulk Data Processing

Laravel Notification System

AJAX Form Handling

DataTables Integration

Asynchronous Backend Processing

Error Handling with Failed Jobs

👨‍💻 Author

Fardin FW
Backend Developer

📄 License

This project is open-source and available under the MIT License.