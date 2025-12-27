# Athka AuthKit

Reusable authentication UI module for Laravel that provides:
- Login page
- Forgot password page
- Reset password page
- Password reset email template
- Optional trait to send the package reset notification

This package is designed to be reused across multiple Laravel projects without copy-pasting auth views/controllers.

---

## Requirements

- PHP: ^8.2
- Laravel: 10 / 11 / 12 (via `illuminate/support` compatibility)

---

## Installation

### Option A) Install from GitHub (recommended)

1) Require the package:

```bash
composer require athka/authkit:dev-main


## Email & Password Reset Requirements (Host Project Checklist)

AuthKit does not ship SMTP credentials. The **host Laravel project** must be configured to send emails and handle password reset tokens.

### 1) Configure SMTP in `.env`
Example (Gmail SMTP):

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
