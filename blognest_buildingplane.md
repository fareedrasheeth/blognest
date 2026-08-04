# BlogNest - Development Roadmap & Building Plan (`blognest_buildingplane.md`)

## 📌 Project Overview & Tech Stack Breakdown

**BlogNest** is a full-stack, hostable blog application built strictly adhering to the **IN2120 Web Programming Assignment** requirements.

### 🛠️ Technology Stack
* **Frontend:** HTML5, Modern Vanilla CSS3 (Custom design system, clean typography, responsive layout), Vanilla JavaScript (ES6+ Fetch API, DOM manipulation, live Markdown parsing via `marked.js`).
* **Backend:** PHP 8+ (PDO for database abstraction, Native PHP Sessions for state management, `password_hash()` / `password_verify()` BCRYPT password encryption).
* **Database:** MySQL (Relational database with Foreign Key constraints and `ON DELETE CASCADE`).
* **Security Standards:** Prepared SQL statements against SQL Injection, strict HTML output escaping against Cross-Site Scripting (XSS), server-side authorization checks for post modification/deletion.

---

## 📁 Proposed Project Structure

```
blognest/
├── config/
│   └── db.php               # Database connection file using PDO
├── api/
│   ├── auth/
│   │   ├── register.php     # Endpoint: User registration
│   │   ├── login.php        # Endpoint: User authentication & session init
│   │   └── logout.php       # Endpoint: Destroy user session
│   └── posts/
│       ├── index.php        # Endpoint: GET (List all or single post), POST (Create post)
│       └── manage.php       # Endpoint: POST/PUT (Update post), DELETE (Delete post)
├── assets/
│   ├── css/
│   │   └── style.css        # Core stylesheet (Design tokens, layout, cards, forms)
│   └── js/
│       ├── main.js          # Main UI controller & global utilities
│       ├── auth.js          # Form handling & validation for login/register
│       ├── posts.js         # Post fetching, rendering, and interaction logic
│       └── editor.js        # Markdown editor & live preview controller
├── includes/
│   ├── header.php           # Global navigation header component
│   ├── footer.php           # Global footer component
│   └── functions.php        # Helper functions (session check, ownership validation, XSS sanitizer)
├── schema.sql               # MySQL database structure definition
├── index.php                # Home page (lists all blog posts with previews)
├── post.php                 # Single blog post detail view
├── editor.php               # Dual-purpose Blog post Editor (Create / Edit)
├── login.php                # User login page
├── register.php             # User registration page
├── README.md                # Setup, deployment guide & API reference
├── blognest_build.md        # Original assignment specifications
└── blognest_buildingplane.md # Detailed execution schedule (This document)
```

---

## 🗓️ 4-Week Development Schedule

### **Week 1: Database Architecture & Core Authentication System**
> **Focus:** Setting up the foundation, database schema, database configuration, and secure user management.

* **Task 1.1: Project Skeleton & Database Schema**
  * Create `schema.sql` containing `user` and `blogPost` table definitions with `FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE`.
  * Set up database instance locally (XAMPP / WAMP / MySQL Server).
* **Task 1.2: Secure Database Connection (`config/db.php`)**
  * Implement PDO connection class/script with error modes, charset settings (`utf8mb4`), and exception handling.
* **Task 1.3: User Registration Backend (`api/auth/register.php`)**
  * Receive user registration data (username, email, password).
  * Validate inputs on server side (unique email/username, required fields).
  * Securely hash passwords using `password_hash($password, PASSWORD_BCRYPT)`.
  * Store user record via prepared statement.
* **Task 1.4: User Login & Session Management (`api/auth/login.php` & `logout.php`)**
  * Verify user credentials via `password_verify()`.
  * Store `user_id`, `username`, and `role` in `$_SESSION`.
  * Create logout endpoint to safely destroy session cookies.
* **Week 1 Deliverable Check:** Functional database script + working PHP auth endpoints (tested via cURL / Postman / forms).

---

### **Week 2: Backend Blog Management & Server-Side Authorization**
> **Focus:** Implementing blog CRUD endpoints and enforcing strict server-side ownership verification.

* **Task 2.1: Post Listing & Retrieval Endpoints (`GET /api/posts/index.php`)**
  * Endpoint to fetch all posts with author username, formatted creation date, and post content/excerpt.
  * Endpoint to fetch a single post by `id`.
* **Task 2.2: Post Creation Endpoint (`POST /api/posts/index.php`)**
  * Verify user session (`auth_check`).
  * Sanitize and validate title and content.
  * Insert new post with `user_id` linked to the active session ID.
* **Task 2.3: Post Update & Delete Endpoints with Ownership Verification (`api/posts/manage.php`)**
  * Enforce Server-Side Ownership Check: Fetch `user_id` of target post and verify `post.user_id === $_SESSION['user_id']`.
  * Return `403 Forbidden` JSON response if an unauthorized user attempts to edit/delete another user's post.
  * Perform UPDATE / DELETE operations using prepared statements.
* **Week 2 Deliverable Check:** Full RESTful/JSON PHP backend API with session-based ownership control verified.

---

### **Week 3: Frontend UI/UX, Markdown Integration & Page Assembly**
> **Focus:** Crafting a modern, responsive user interface and connecting frontend components to backend endpoints.

* **Task 3.1: CSS Design System (`assets/css/style.css`)**
  * Build modern, clean styling (color system, responsive grid, clean typography, dynamic hover states, card UI).
  * Ensure mobile-friendly responsive layout across viewports.
* **Task 3.2: Authentication Views (`login.php` & `register.php`)**
  * Build clean form interfaces with client-side validation (`assets/js/auth.js`).
  * Connect forms to backend auth endpoints using `fetch()` AJAX API.
* **Task 3.3: Home Page & Single Post View (`index.php` & `post.php`)**
  * `index.php`: Dynamically render post cards (title, excerpt, author, date link).
  * `post.php`: Display full blog post content. Render Markdown to HTML using `marked.js`.
  * Conditionally render **Edit** and **Delete** buttons ONLY if logged-in user is the author of the post.
* **Task 3.4: Blog Editor Page (`editor.php`)**
  * Dual-purpose form for both creating new posts and editing existing posts.
  * Integrates live Markdown preview functionality (`assets/js/editor.js`).
  * Submits post data to create/update endpoints with feedback notifications.
* **Week 3 Deliverable Check:** Entire web application fully functional locally in browser.

---

### **Week 4: Quality Assurance, Security Audit, Documentation & Submission**
> **Focus:** Polish, security hardening, README documentation, and final deployment setup.

* **Task 4.1: Security & Output Hardening**
  * Escaping output via `htmlspecialchars()` to prevent XSS attacks when displaying user-generated data.
  * Confirm prepared statements cover 100% of SQL operations.
* **Task 4.2: Project Documentation (`README.md`)**
  * Document local setup instructions (XAMPP/WAMP database setup, config steps).
  * Write step-by-step free hosting deployment guide (InfinityFree / 000WebHost).
* **Task 4.3: Manual Submission & Demonstration Checklist (User Tasks)**
  * **Public Hosting:** Deploy application & database to a free PHP/MySQL host (e.g. InfinityFree) and verify live public URL.
  * **Demo Video:** Record 3-minute screen capture demonstrating Registration, Login, Post Creation, Editing, Deleting, and Public URL access.
  * **GitHub Push:** Push complete codebase to GitHub repository.
  * **Submission Folder:** Assemble PDF with links, MP4 video, and source code into zipped folder named with student index number.

---

## ⚡ Next Steps

To begin execution when you are ready:
1. Review and approve this building plan.
2. We will start with **Week 1 (Database schema `schema.sql`, connection setup, and authentication endpoints)**.
