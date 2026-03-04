# F Earning Platform

A complete micro-earning platform built with PHP and MySQL.

## Features
- User Registration & Login (Secure hashing)
- Ad Watching System with timer & reward validation
- Referral System with commission tracking
- Withdrawal System (Bkash, Nagad, PayPal)
- Admin Panel (User, Ad, and Withdrawal management)
- Dark/Light Mode
- SQL Injection Protection & Sanitization

## Setup Instructions

1. **Database Setup**:
   - Create a database named `f_earning_db` in PHPMyAdmin.
   - Import the SQL schema from `@/C:/xampp/htdocs/ads/sql/schema.sql`.

2. **Configuration**:
   - Update database credentials in `@/C:/xampp/htdocs/ads/config/config.php` if different from default.

3. **Admin Access**:
   - To create an admin account, register a normal user first.
   - Manually change the `is_admin` field to `1` in the `users` table for that user.

4. **Running**:
   - Place the folder in your `htdocs` (XAMPP) or `www` (WAMP) directory.
   - Access via `http://localhost/ads`.

## Folder Structure
- `admin/`: Admin panel files
- `api/`: Backend API endpoints (JSON responses)
- `assets/`: CSS, JS, and Image assets
- `config/`: Database configuration
- `includes/`: Core logic and helper functions
- `sql/`: Database schema
- `user/`: User dashboard and ad system
