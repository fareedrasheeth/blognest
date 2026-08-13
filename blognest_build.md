# Project prompt: Blog Application (IN2120 Web Programming Assignment)

## Objective
Build a full-stack **Blog Application** with:
- **Frontend:** HTML, CSS, JavaScript (vanilla)
- **Backend:** PHP
- **Database:** MySQL

The app must be a complete, working, hostable project — not a prototype. Follow the requirements below exactly, then propose a file/folder structure and start implementing.

---

## 1. User Authentication & Authorization
- Users must be able to **register**, **log in**, and **log out**.
- Passwords must be securely hashed (use PHP's `password_hash()` / `password_verify()` — never store plaintext).
- Use PHP sessions to track logged-in users.
- Only authenticated users can **create**, **update**, or **delete** blog posts.
- A user must **only** be able to edit or delete **their own** blog posts — enforce this check server-side (compare `user_id` on the post with the logged-in session user's id), not just in the UI.
- Unauthenticated users can still view the home page and individual blog posts (read-only).

## 2. Blog Management
Authenticated users should be able to:
- **Create** a new blog post via a blog editor. A Markdown editor is acceptable (e.g., render Markdown to HTML on save or on display — a simple client-side Markdown parser like `marked.js` is fine).
- **Read** blog posts — all blogs listed on the home page.
- **Update** their own blog posts.
- **Delete** their own blog posts.

## 3. Frontend Features
- **Home page:** lists all blog posts (title, short excerpt/preview, author, date), links to each full post.
- **Single blog view page:** shows full blog content, author name, and created/updated date.
- **Blog editor page:** used for both creating a new post and editing an existing one.
- **Responsive, clean UI** built with plain HTML/CSS/JS — no framework required, but should look polished (good typography, spacing, mobile-friendly layout).
- Reasonable client-side validation (e.g., required fields) in addition to server-side validation.

## 4. Database Schema (MySQL)
At minimum, implement these two tables:

```sql
CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user'
);

CREATE TABLE blogPost (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);
```

- Use **prepared statements** (PDO or MySQLi) for all queries to prevent SQL injection.
- Escape/sanitize all output to prevent XSS (especially rendered blog content).

## 5. Backend (PHP) Requirements
- Organize backend code sensibly (e.g., `/api` or `/backend` folder with endpoints for auth, posts).
- Suggested endpoints:
  - `POST /register.php`
  - `POST /login.php`
  - `POST /logout.php`
  - `GET /posts.php` (list all)
  - `GET /posts.php?id=` (single post)
  - `POST /posts.php` (create — auth required)
  - `PUT`/`POST /posts.php?id=` (update — auth + ownership required)
  - `DELETE /posts.php?id=` (delete — auth + ownership required)
- Return JSON responses for AJAX-driven pages, or use standard form posts with server-side redirects — pick one consistent approach.
- Store DB credentials in a separate config file (e.g., `config.php`), not hardcoded across files.

## 6. Hosting Requirement
- Deploy the application so it's accessible via a **public URL** (e.g., InfinityFree, 000WebHost, or similar free PHP/MySQL hosting).
- The hosted version must have **feature parity** with the local version — test registration, login, CRUD, and viewing on the live URL before submission.

## 7. Demonstration Video
- Record a **3-minute** screen-capture video (MP4) showing:
  - User registration & login
  - Creating, updating, and deleting a blog post
  - Viewing blogs (list page + single blog view)
  - Access to the hosted website (public URL)

## 8. Submission Package
- Push the full source code to a **GitHub** repository.
- Create a **PDF** containing:
  - GitHub repository link
  - Hosted website link (must be working)
- Combine into a single folder:
  - Source code folder
  - The PDF document
  - The MP4 demo video
- Rename the folder with the student index number.
- **Zip** the folder and submit before the deadline.

---

## Instructions for the Coding Agent
1. Propose a clean project structure (frontend, backend/api, config, db schema file, assets).
2. Implement the MySQL schema as a `.sql` file (`schema.sql`) matching section 4 above.
3. Build the PHP backend first (auth + CRUD endpoints) with prepared statements and session-based auth/ownership checks.
4. Build the frontend pages: home (list), single post view, editor (create/update), login, register.
5. Wire the frontend to the backend (fetch/AJAX or form submissions).
6. Add basic responsive CSS styling.
7. Include a `README.md` with setup instructions (DB import, config, local run steps) and notes on how to deploy to a free PHP/MySQL host.
8. Do not include hosting deployment, video recording, or the final zip packaging in code — those are manual steps I'll do myself after the code is complete.
