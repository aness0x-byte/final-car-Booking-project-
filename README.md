# 🚗 DriveEase — PHP + MySQL Car Rental Site

A complete dynamic car rental website built with:
**HTML · PHP · JavaScript · CSS · MySQL**

---

## 📁 File Structure

```
php_app/
├── index.php           → Homepage (car listing + search)
├── login.php           → Sign in page
├── register.php        → Create account page
├── logout.php          → Sign out handler
├── booking.php         → Book a car (login required)
├── my-bookings.php     → View your reservations (login required)
│
├── config/
│   └── db.php          → MySQL database connection (PDO)
│
├── includes/
│   ├── header.php      → Shared navbar (included on all pages)
│   └── footer.php      → Shared footer (included on all pages)
│
├── css/
│   └── style.css       → All styles (responsive, orange theme)
│
├── js/
│   └── main.js         → Search, filter, price calculator, animations
│
└── sql/
    └── schema.sql      → MySQL table definitions + sample data
```

---

## ⚡ Setup in 5 Steps

### Step 1 — Copy to XAMPP
Copy the entire `php_app/` folder to:
```
C:\xampp\htdocs\php_app\
```

### Step 2 — Start XAMPP
Open XAMPP Control Panel and click **Start** for:
- ✅ Apache
- ✅ MySQL

### Step 3 — Create the Database
1. Open your browser: **http://localhost/phpmyadmin**
2. Click **"New"** (left sidebar)
3. Name it: `driveease` → click **Create**
4. Click the `driveease` database
5. Click the **SQL** tab at the top
6. Open `sql/schema.sql`, copy ALL the content
7. Paste it into the SQL box → click **Go**

✅ You should now see 3 tables: `users`, `cars`, `bookings`

### Step 4 — Check DB Settings
Open `config/db.php` — the defaults work for XAMPP:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'driveease');
define('DB_USER', 'root');
define('DB_PASS', '');       // XAMPP has no password by default
```

### Step 5 — Open the Site
Go to: **http://localhost/php_app/**

---

## 🔑 Demo Login

| Email | Password | Role |
|-------|----------|------|
| admin@driveease.com | password | Admin |

Or click **Register** to create a new account.

---

## 🌐 Pages & Features

| Page | URL | Feature |
|------|-----|---------|
| Homepage | `/index.php` | Car grid, live search, category filters |
| Login | `/login.php` | Email + password login, session management |
| Register | `/register.php` | Account creation, password hashing |
| Logout | `/logout.php` | Destroys session |
| Book a Car | `/booking.php?car_id=1` | Date picker, price calculator, DB insert |
| My Bookings | `/my-bookings.php` | Booking history with JOIN query |

---

## 💡 Common Edits

**Change service fee (booking total):**
→ Edit `booking.php` line with `$serviceFee = 10`
→ Also update in `js/main.js`: `const serviceFee = 10`

**Add a new car:**
```sql
INSERT INTO cars (name, brand, category, price, image_url)
VALUES ('Toyota Camry', 'Toyota', 'Sedan', 55.00, 'https://...');
```
Run this in phpMyAdmin SQL tab.

**Change brand colors:**
→ Edit `css/style.css` → find `--orange: #f97316;` and change the hex

**Add a new page:**
1. Create `mypage.php`
2. Add `require_once 'config/db.php';` at top
3. Add `require_once 'includes/header.php';` for the navbar
4. Add your HTML content
5. Add `require_once 'includes/footer.php';` at the bottom
6. Link it in `includes/header.php`

---

## 🔒 Security Features Used
- `password_hash()` — bcrypt password hashing (never stores plain text)
- `password_verify()` — safely checks passwords
- PDO Prepared Statements — prevent SQL injection
- `htmlspecialchars()` — prevent XSS attacks
- `session_regenerate_id()` — prevent session fixation
- Login guards on restricted pages (`booking.php`, `my-bookings.php`)
