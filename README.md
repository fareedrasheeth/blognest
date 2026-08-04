# BlogNest - Full-Stack Blog Application

**IN2120 Web Programming Assignment**

BlogNest is a modern, hostable full-stack blogging platform built with **Vanilla HTML/CSS/JS**, **PHP (PDO)**, and **MySQL**.

---

## 🌟 Key Features
- **User Authentication & Authorization:** Registration, Login, Logout using BCRYPT hashed passwords (`password_hash()`) and PHP native sessions.
- **Blog CRUD Management:** Authenticated users can Create, Read, Update, and Delete blog posts.
- **Server-Side Ownership Enforcement:** Server strictly checks `post.user_id === $_SESSION['user_id']` for edit/delete requests.
- **Markdown Support & Live Preview:** Post editor supports Markdown formatting with client-side live preview (`marked.js`).
- **Security Protections:** Prepared statements (PDO) to block SQL Injection, output escaping (`htmlspecialchars()`) to prevent XSS attacks.
- **Modern Responsive UI:** Glassmorphism card layouts, fluid typography, dark/light harmonious color system, and mobile responsiveness.

---

## 📁 File & Directory Structure

```
blognest/
├── config/
│   └── db.php               # Database connection configuration (PDO)
├── api/
│   ├── auth/
│   │   ├── register.php     # Endpoint: User registration
│   │   ├── login.php        # Endpoint: User login & session setup
│   │   ├── logout.php       # Endpoint: Destroy user session
│   │   └── me.php           # Endpoint: Check active session user
│   └── posts/
│       ├── index.php        # Endpoint: List posts, single post, create post
│       └── manage.php       # Endpoint: Update & Delete post with ownership check
├── assets/
│   ├── css/
│   │   └── style.css        # Responsive stylesheet & design system
│   └── js/
│       ├── main.js          # Global UI utilities & logout handler
│       ├── auth.js          # Registration & Login AJAX handlers
│       ├── posts.js         # Post dynamic interaction & deletion handler
│       └── editor.js        # Post form & Live Markdown preview controller
├── includes/
│   ├── header.php           # Shared header & navigation component
│   ├── footer.php           # Shared footer component
│   └── functions.php        # Helper functions & security utilities
├── schema.sql               # MySQL database schema definition file
├── index.php                # Home page (Lists all blog posts)
├── post.php                 # Single blog post detail view
├── editor.php               # Dual-purpose Blog post editor (Create / Edit)
├── login.php                # User login page
├── register.php             # User registration page
└── README.md                # Project documentation & deployment guide
```

---

## 🚀 Local Installation & Setup Instructions

### Prerequisites
* **Web Server:** XAMPP, WAMP, MAMP, or PHP Built-in Server with MySQL / MariaDB.
* **PHP:** Version 7.4 or 8.x (with `pdo_mysql` extension enabled).

### Step 1: Clone / Copy Project
Place the `blognest` folder inside your local web server root directory (e.g. `C:\xampp\htdocs\blognest`).

### Step 2: Import MySQL Database Schema
1. Open **phpMyAdmin** (`http://localhost/phpmyadmin`) or your MySQL CLI client.
2. Import the `schema.sql` file provided in the repository root:
   ```sql
   SOURCE path/to/blognest/schema.sql;
   ```
   *This automatically creates `blognest_db` with `user` and `blogPost` tables.*

### Step 3: Configure Database Connection
Open `config/db.php` and verify/update your MySQL credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'blognest_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Step 4: Run Application Locally
* **Option A (XAMPP/WAMP):** Navigate to `http://localhost/blognest/` in your browser.
* **Option B (PHP Built-in Dev Server):** Open terminal inside `blognest` directory and run:
  ```bash
  php -S localhost:8000
  ```
  Then visit `http://localhost:8000/`.

---

## 🌐 Hosting Deployment Guide (Free PHP/MySQL Hosts)

### Deploying to InfinityFree / 000WebHost:
1. **Create Account & Account Domain:** Sign up at [InfinityFree](https://www.infinityfree.com/) or 000WebHost and create a free website domain.
2. **Database Setup:**
   - Go to Control Panel -> **MySQL Databases**.
   - Create a database and database user. Note down the Database Hostname, Database Name, Database Username, and Password.
   - Open **phpMyAdmin** in the hosting panel and import `schema.sql`.
3. **Upload Files:**
   - Use **File Manager** or an FTP client (FileZilla) to upload all files to the `htdocs` (or `public_html`) folder.
4. **Update DB Config:**
   - Edit `config/db.php` on the server to match your hosting database credentials.
5. **Verify Host Feature Parity:**
   - Register a new account on the live URL.
   - Login, create a post with Markdown, edit the post, view it on home page, and delete it.

---

## 📋 Submission Checklist (Student Instructions)

Before submitting your final assignment, ensure you complete the following:

1. [ ] **Public Hosted URL:** Host the app live on InfinityFree/000WebHost and test functionality.
2. [ ] **GitHub Repository:** Push the entire codebase to a public/private GitHub repository.
3. [ ] **3-Minute MP4 Video Demo:** Record a 3-minute screen capture video showing:
   - User Registration & Login
   - Creating a new post with Markdown
   - Updating & Deleting your post
   - Viewing post list on home page & single post page
   - Showing the working public hosted URL in browser URL bar
4. [ ] **PDF Document:** Create a single PDF containing:
   - GitHub Repository Link
   - Live Hosted Website URL
5. [ ] **Final Submission Package:**
   - Create a master folder named after your **Student Index Number** (e.g. `IN2120_20000000`).
   - Place inside:
     - Source code folder (`blognest/`)
     - The PDF document
     - The MP4 demo video
   - Zip the parent folder and upload to the assignment portal before deadline.
