# Seven Up Link · 7UL.ir

<p align="center">
  <strong>سرویس کوتاه‌کننده لینک با آمار، QR و پنل مدیریت</strong><br>
  <strong>URL shortener with analytics, QR codes, and admin panel</strong>
</p>

<p align="center">
  <a href="#فارسی">فارسی</a> · <a href="#english">English</a>
</p>

---

<a id="فارسی"></a>

## فارسی

### درباره

**Seven Up Link (7UL.ir)** یک سرویس کوتاه‌کننده لینک است که به کاربران امکان می‌دهد لینک‌های طولانی را کوتاه کنند، آمار کلیک را ببینند، QR تولید کنند و از طریق API لینک بسازند. ورود با OTP موبایل، گزارش لینک‌های مشکوک و پنل ادمین کامل از دیگر بخش‌های سامانه است.

### قابلیت‌ها

- **کوتاه‌سازی لینک** — ایجاد لینک ۸ کاراکتری با QR قابل دانلود
- **انواع لینک** — `link`، `utm`، `iframe`، `code`، `text`
- **آمار بازدید** — IP، دستگاه، مرورگر و سیستم‌عامل
- **ورود با OTP** — احراز هویت با شماره موبایل و پیامک
- **پنل کاربری** — مدیریت لینک‌ها، آمار و توکن API
- **REST API** — ایجاد و مشاهده لینک‌ها با توکن اختصاصی
- **گزارش لینک** — ثبت و پیگیری لینک‌های مشکوک
- **پنل ادمین** — کاربران، لینک‌ها، گزارش‌ها، تنظیمات، پشتیبان‌گیری و اجرای دستورات

### فناوری‌ها

| لایه | ابزار |
|------|-------|
| Backend | Laravel 13 · PHP 8.4 |
| Frontend | Livewire 4 · Tailwind CSS 4 · Flexiwind |
| Auth | Spatie One-Time Passwords · Spatie Permission |
| تاریخ | morilog/jalali |
| QR | endroid/qr-code |
| پشتیبان | spatie/laravel-backup |

### پیش‌نیازها

- PHP 8.4+
- Composer
- Node.js 20+ و npm
- MySQL 8+
- Redis (اختیاری)

### نصب

```bash
git clone <repository-url> 7ul
cd 7ul
composer setup
```

یا به‌صورت دستی:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### پیکربندی

فایل `.env` را ویرایش کنید:

```dotenv
APP_NAME="Seven Up Link"
APP_URL=https://7ul.ir

DB_DATABASE=7ul
DB_USERNAME=root
DB_PASSWORD=

# ارسال OTP
SMS_TOKEN=
SMS_GATEWAY=
SMS_URL=https://srscrm.ir/api/sms/send
```

### ایجاد ادمین

```bash
php artisan app:set-user-as-admin
# یا با شماره موبایل:
php artisan app:set-user-as-admin 09123456789
```

دستور کاربر را ایجاد می‌کند (در صورت نبود) و نقش `admin` را به او اختصاص می‌دهد.

### توسعه

```bash
composer dev
```

این دستور سرور Laravel، صف، لاگ و Vite را هم‌زمان اجرا می‌کند.

```bash
php artisan serve          # فقط سرور
npm run dev                # فقط Vite
```

### تست

```bash
composer test
# یا
php artisan test --compact
```

### API

پایه: `{APP_URL}/api/v1`

| متد | مسیر | توضیح |
|-----|------|-------|
| `GET` | `/links` | لیست لینک‌های کاربر |
| `POST` | `/links` | ایجاد لینک کوتاه |
| `GET` | `/links/{shortCode}` | جزئیات لینک |
| `GET` | `/links/{shortCode}/stats` | آمار بازدید |

احراز هویت با هدر:

```
Authorization: Bearer {api_token}
```

توکن API از پنل کاربری (`/user/api`) قابل مشاهده و تولید مجدد است.

---

<a id="english"></a>

## English

### About

**Seven Up Link (7UL.ir)** is a URL shortening service that lets users shorten long links, track click analytics, generate QR codes, and create links via API. Mobile OTP login, suspicious link reporting, and a full admin panel are included.

### Features

- **Link shortening** — 8-character short codes with downloadable QR codes
- **Link types** — `link`, `utm`, `iframe`, `code`, `text`
- **Visit analytics** — IP, device, browser, and OS tracking
- **OTP login** — mobile number authentication via SMS
- **User dashboard** — manage links, stats, and API tokens
- **REST API** — create and inspect links with a personal token
- **Link reporting** — submit and track suspicious links
- **Admin panel** — users, links, reports, settings, backups, and command runner

### Tech stack

| Layer | Tools |
|-------|-------|
| Backend | Laravel 13 · PHP 8.4 |
| Frontend | Livewire 4 · Tailwind CSS 4 · Flexiwind |
| Auth | Spatie One-Time Passwords · Spatie Permission |
| Dates | morilog/jalali |
| QR | endroid/qr-code |
| Backup | spatie/laravel-backup |

### Requirements

- PHP 8.4+
- Composer
- Node.js 20+ and npm
- MySQL 8+
- Redis (optional)

### Installation

```bash
git clone <repository-url> 7ul
cd 7ul
composer setup
```

Or step by step:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### Configuration

Edit `.env`:

```dotenv
APP_NAME="Seven Up Link"
APP_URL=https://7ul.ir

DB_DATABASE=7ul
DB_USERNAME=root
DB_PASSWORD=

# OTP delivery
SMS_TOKEN=
SMS_GATEWAY=
SMS_URL=https://srscrm.ir/api/sms/send
```

### Create an admin

```bash
php artisan app:set-user-as-admin
# or with a mobile number:
php artisan app:set-user-as-admin 09123456789
```

The command creates the user if needed and assigns the `admin` role.

### Development

```bash
composer dev
```

Runs the Laravel server, queue worker, log tail, and Vite together.

```bash
php artisan serve          # server only
npm run dev                # Vite only
```

### Testing

```bash
composer test
# or
php artisan test --compact
```

### API

Base URL: `{APP_URL}/api/v1`

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/links` | List the authenticated user's links |
| `POST` | `/links` | Create a short link |
| `GET` | `/links/{shortCode}` | Show link details |
| `GET` | `/links/{shortCode}/stats` | Visit statistics |

Authenticate with:

```
Authorization: Bearer {api_token}
```

The API token is available in the user panel at `/user/api`.

---

## License

This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
