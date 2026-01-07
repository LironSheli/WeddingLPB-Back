# Backend Setup Guide

## 📋 Prerequisites

-   PHP 8.2+
-   Composer
-   MySQL/PostgreSQL
-   Node.js (for frontend)

## 🚀 Installation Steps

### 1. Install Dependencies

```bash
cd backend/WeddingLPB-Back
composer install
```

### 2. Environment Configuration

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure `.env` File

Add these environment variables:

```env
APP_NAME="WeddingLPB"
APP_URL=http://localhost:8000
APP_FRONTEND_URL=http://localhost:3000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=weddinglpb
DB_USERNAME=root
DB_PASSWORD=

# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback

# Gemini AI
GEMINI_API_KEY=your_gemini_api_key

# Lemon Squeezy
LEMON_SQUEEZY_API_KEY=your_api_key
LEMON_SQUEEZY_WEBHOOK_SECRET=your_webhook_secret
LEMON_SQUEEZY_STORE_ID=your_store_id

# SMTP Email
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USER=your_smtp_user
SMTP_PASSWORD=your_smtp_password

MAIL_MAILER=smtp
MAIL_HOST=${SMTP_HOST}
MAIL_PORT=${SMTP_PORT}
MAIL_USERNAME=${SMTP_USER}
MAIL_PASSWORD=${SMTP_PASSWORD}
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@weddinglpb.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Install Sanctum

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 6. Create Storage Link

```bash
php artisan storage:link
```

### 7. Start the Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`

## 🔧 Additional Setup

### Queue Worker (for emails)

```bash
php artisan queue:work
```

### Scheduler (for expired pages)

Add to your crontab or run:

```bash
php artisan schedule:work
```

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── WeddingPageController.php
│   │   ├── PaymentController.php
│   │   └── AIController.php
│   └── Middleware/
│       └── Cors.php
├── Models/
│   ├── User.php
│   ├── WeddingPage.php
│   ├── RevisionRequest.php
│   └── Payment.php
├── Mail/
│   ├── PreviewReadyMail.php
│   └── PaymentConfirmationMail.php
└── Services/
    └── ContentBuilderService.php

database/migrations/
├── 0001_01_01_000000_create_users_table.php
├── 2024_01_01_000001_create_wedding_pages_table.php
├── 2024_01_01_000002_create_revision_requests_table.php
└── 2024_01_01_000003_create_payments_table.php

routes/
└── api.php
```

## 🔐 API Endpoints

### Authentication

-   `POST /api/register` - Register new user
-   `POST /api/login` - Login
-   `POST /api/logout` - Logout
-   `GET /api/user` - Get current user
-   `GET /api/auth/google` - Google OAuth redirect
-   `GET /api/auth/google/callback` - Google OAuth callback

### Wedding Pages

-   `GET /api/wedding-pages` - List user's pages
-   `POST /api/wedding-pages` - Create new page
-   `GET /api/wedding-pages/{slug}` - Get page by slug
-   `PUT /api/wedding-pages/{id}` - Update page
-   `PUT /api/wedding-pages/{id}/design` - Update design
-   `POST /api/wedding-pages/{id}/revisions` - Request revision

### Payments

-   `POST /api/wedding-pages/{id}/payment` - Generate payment link
-   `POST /api/payments/webhook` - Lemon Squeezy webhook

### AI

-   `POST /api/ai/generate-image` - Generate AI image
-   `POST /api/ai/generate-text` - Generate AI text

## 🧪 Testing

```bash
php artisan test
```

## 📝 Notes

-   Make sure CORS is configured correctly for your frontend URL
-   Sanctum uses session-based authentication
-   Queue worker must be running for emails to be sent
-   Scheduler must be running to automatically expire pages
