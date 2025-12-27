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
