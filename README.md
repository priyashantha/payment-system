# Full Stack Delivery Management System

A full-stack Dockerized Delivery Management app built with **Laravel**, **MySQL**, **React**, and **Tailwind CSS**.

---

## Environment Setup

### 1. Copy and Configure Environment Files

Backend `.env`:
```bash
cp backend/.env.example backend/.env
```

Then update it if necessary (for local MinIO, database, etc.):
```env
APP_NAME="Delivery Manager"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=payout_system
DB_USERNAME=root
DB_PASSWORD=root

QUEUE_CONNECTION=database

# Local S3 (MinIO)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadmin
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=laravel-payments
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
```

Frontend `.env`:
```bash
VITE_API_URL=http://localhost:8000/api
VITE_RECAPTCHA_SITE_KEY=YOUR_RECAPTCHA_SITE_KEY
```

---

### 2. Build and Start Containers

```bash
docker-compose up --build -d
```

This will start:
- Laravel API (`backend`)
- React app (`frontend`)
- MySQL
- MinIO (S3-compatible storage)
- Mailtrap (optional, for local email testing)

---

### 3. Install Laravel Dependencies

```bash
docker run --rm -v $(pwd)/backend:/app -w /app composer composer install
```

---

### 4. Generate Laravel App Key

```bash
docker-compose exec backend php artisan key:generate
```

---

### 5. Run Database Migrations and Seeders

```bash
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan db:seed
```

---

### 6. Test Customer Logins


### 7. Run Queue Workers
You can log in to the system using the customer email and the default password secret created during import or seeding.

| Field    | Example                |
|----------|------------------------|
| Email    | `customer@example.com` |
| Password | `secret`               |

This project uses queued jobs for:
- Processing uploaded payment files
- Chunk-based payment imports
- Invoice generation and emailing

To process the queue locally:

```bash
docker-compose exec backend php artisan queue:work --tries=3
```

Or to run multiple workers for speed:

```bash
# 3 parallel workers
docker-compose exec -d backend php artisan queue:work --tries=3 &
docker-compose exec -d backend php artisan queue:work --tries=3 &
docker-compose exec -d backend php artisan queue:work --tries=3 &
```

To clear and restart jobs:

```bash
docker-compose exec backend php artisan queue:restart
docker-compose exec backend php artisan queue:clear
```

---

### 8. (Optional) Using Redis for Queues

For better performance, switch to Redis:

```bash
composer require predis/predis
```

Update `.env`:
```env
QUEUE_CONNECTION=redis
```

Restart the queue workers:
```bash
docker-compose exec backend php artisan queue:work redis --tries=3
```

---

## Local S3 Storage with MinIO

MinIO emulates AWS S3 and is included in `docker-compose.yml`.

**Web Console:** [http://localhost:9001](http://localhost:9001)  
**Login:** `minioadmin / minioadmin`

Create a bucket named `laravel-payments`.

Test from inside the container:
```bash
php artisan tinker
>>> Storage::disk('s3')->put('uploads/test.txt', 'Hello MinIO!');
```

---

## Running the Frontend

```bash
cd frontend
npm install
npm run dev
```

Visit the app at:
[http://localhost:5173](http://localhost:5173)

---

## Automating Daily Payouts

Daily payouts, invoice generation, and email dispatching are triggered by the custom Artisan command:

```bash
php artisan payouts:run
```

### Set Up Cron Job (inside the backend container)

Example crontab (inside container):
```bash
0 23 * * * cd /var/www/html && php artisan payouts:run >> /dev/null 2>&1
```

---

## Email Setup (Mailtrap Sandbox)

For local email testing:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="Payout System"
```

---

## ✅ Verify the Setup

1. Visit [http://localhost:5173](http://localhost:5173)
2. Upload a payment CSV (stored in MinIO)
3. Watch the jobs process:
   ```bash
   docker-compose exec backend php artisan queue:work
   tail -f backend/storage/logs/laravel.log
   ```
4. Check processed records in the database or in MinIO.

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| **Files not showing in MinIO** | Ensure bucket `laravel-payments` exists. |
| **Slow processing** | Increase workers or switch to Redis queue. |
| **Emails not sent** | Verify Mailtrap credentials or mailer config. |

---

## Useful Commands

| Action | Command |
|---------|----------|
| Run queue | `php artisan queue:work --tries=3` |
| Restart queues | `php artisan queue:restart` |
| Migrate fresh | `php artisan migrate:fresh --seed` |
| Check scheduled jobs | `php artisan schedule:list` |
| Test S3 | `php artisan tinker` → `Storage::disk('s3')->put('test.txt', 'hi');` |

---

## Summary

Your local environment now supports:
- Laravel + React full-stack app
- File uploads via MinIO (S3-compatible)
- Background queue jobs for large imports
- Automatic locale-based number parsing (via intl)
- Scalable worker setup for high-volume processing

---

## Local URLs

| Service | URL |
|----------|-----|
| Frontend | [http://localhost:5173](http://localhost:5173) |
| Backend API | [http://localhost:8000/api](http://localhost:8000/api) |
| MinIO Console | [http://localhost:9001](http://localhost:9001) |

---
### Author
Developed for internal testing and demonstration of distributed payment ingestion, background processing, and Laravel queue scaling.
