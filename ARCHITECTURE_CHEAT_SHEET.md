# Medora Project Architecture Cheat Sheet

This is a simple revision guide for viva/demo questions.

## 1. What type of project is this?

This is a **plain PHP web application**.

It does **not** use Laravel, CodeIgniter, Symfony, or any third-party framework.

It follows a **simple MVC-like structure**:

- `pages/` = page modules and route handlers
- `*.controller.php` = request handling logic
- `*.model.php` = database/business logic
- `*.layout.php` = UI / HTML rendering
- `core/` = shared helpers like auth, request, response, pharmacy context
- `config/` = environment and database configuration

So if someone asks:

`What architecture did you use?`

You can answer:

`I used a lightweight custom PHP MVC-style structure without third-party frameworks. Controllers handle requests, models handle database logic, layouts render the views, and shared reusable logic is placed in the core folder.`

## 2. How does routing work?

Main entry point:

- `index.php`

How it works:

1. Defines `ROOT` and `APP_BASE`
2. Loads core/config files
3. Boots pharmacy session context
4. Reads the current URL path using `Request::path()`
5. Maps the URL to a file inside `pages/.../index.php`
6. If the page file exists, it includes it
7. Otherwise it loads `pages/404.php`

Example:

- URL `/patient/messages`
- loads `pages/patient/messages/index.php`

So this project uses a **front controller pattern** with file-based routing.

## 3. What is the request flow?

Typical request flow:

1. Browser sends request
2. `index.php` receives it
3. Matching `pages/.../index.php` file is included
4. That file usually includes a role guard like `patient.head.php`
5. Controller logic runs
6. Model methods query/update the database
7. Response is returned as:
   - HTML page
   - redirect
   - JSON

Simple explanation:

`The project starts from index.php, resolves the page path, checks authentication if needed, calls controller/model logic, and then returns either HTML, JSON, or a redirect.`

## 4. What are Request and Response used for?

Files:

- `core/Request.php`
- `core/Response.php`

These are helper classes to keep controllers consistent.

`Request` handles:

- `Request::isPost()`
- `Request::isGet()`
- `Request::post()`
- `Request::get()`
- `Request::expectsJson()`
- `Request::path()`

`Response` handles:

- `Response::redirect()`
- `Response::json()`
- `Response::empty()`
- `Response::status()`
- `Response::abort()`

If asked why these are useful:

`They reduce repeated raw PHP code like $_SERVER checks, header('Location'), echo json_encode, and exit. That makes the code easier to read and maintain.`

## 5. How is authentication handled?

Main file:

- `core/Auth.php`

Authentication is done using **JWT stored in cookies**.

Important points:

- JWT is created using `Auth::sign()`
- JWT is verified using `Auth::decode()`
- Protected pages use `Auth::requireRole('patient')`, `Auth::requireRole('admin')`, etc.
- If the token is invalid, the user is redirected to the correct login page

Role guards are inside:

- `pages/patient/common/patient.head.php`
- `pages/pharmacist/common/pharmacist.head.php`
- `pages/guardian/common/guardian.head.php`
- `pages/admin/common/admin.head.php`

Easy viva answer:

`Authentication is implemented manually using JWT cookies. Each protected module has a head file that verifies the token and role before allowing access.`

## 6. How is database access handled?

Main file:

- `config/database.php`

This project uses **MySQLi with prepared statements**.

Standard methods:

- `Database::fetchOne()` = get one row
- `Database::fetchAll()` = get multiple rows
- `Database::execute()` = insert/update/delete

Important design point:

- Database access is centralized in one class
- Prepared statements are used instead of manual escaping

Good answer:

`The project uses a custom Database class built on MySQLi. All queries now use prepared statements for consistency and safety.`

## 7. What is the role of models?

Model files usually:

- contain SQL queries
- validate business rules
- return arrays/data to controllers

Examples:

- `pages/patient/messages/messages.model.php`
- `pages/pharmacist/inventory/inventory.model.php`
- `pages/admin/settings/settings.model.php`

What to say:

`Models contain the business logic and database interaction. Controllers call model methods instead of writing SQL directly in the page logic.`

## 8. What is the role of controllers?

Controller files usually:

- check request type
- validate input
- call model methods
- return redirect or JSON
- prepare `$data` for the layout

Examples:

- `pages/patient/messages/messages.controller.php`
- `pages/pharmacist/messages/messages.controller.php`
- `pages/admin/settings/settings.controller.php`

What to say:

`Controllers act as the bridge between the request and the model. They process form or AJAX input, call model methods, and prepare the response.`

## 9. What is the role of layouts/views?

Layout files usually:

- render HTML
- use values from `$data`
- contain forms, tables, cards, and page UI

Examples:

- `pages/admin/dashboard/dashboard.layout.php`
- `pages/patient/messages/messages.layout.php`

What to say:

`The layout files work like views. They focus on presenting the UI using data prepared by the controller.`

## 10. How is authorization separated by user role?

The system has multiple roles:

- patient
- guardian
- pharmacist
- admin

Each role has its own area under `pages/`.

Examples:

- `pages/patient/...`
- `pages/guardian/...`
- `pages/pharmacist/...`
- `pages/admin/...`

Each section has:

- its own login flow
- its own head guard
- its own pages/controllers/models

Simple answer:

`The application is role-based. Each user type has a separate module and access is protected by role-specific authentication checks.`

## 11. What is PharmacyContext?

Main file:

- `core/PharmacyContext.php`

This helper manages the multi-pharmacy part of the project.

It handles:

- selected pharmacy in session
- patient pharmacy selection
- pharmacist pharmacy resolution
- pharmacy-scoped filtering

Why it matters:

- different patients and pharmacists may belong to different pharmacies
- data should be filtered by the active pharmacy when needed

Simple answer:

`PharmacyContext is a shared helper that keeps track of the currently active pharmacy and helps isolate pharmacy-specific data.`

## 12. What is ChatMessageSupport?

Main file:

- `core/ChatMessageSupport.php`

This is a shared helper for the messaging system.

It handles:

- chat column checks
- shared SQL select pieces
- insert payload building
- timed fetch helpers
- row mapping for chat messages and contacts

Why it exists:

- patient and pharmacist message models had duplicate logic
- shared helper reduces repetition

Simple answer:

`ChatMessageSupport centralizes common messaging logic so both patient and pharmacist message models can reuse the same helper methods.`

## 13. How is AJAX handled?

AJAX endpoints usually:

- detect AJAX using `Request::expectsJson()`
- return JSON using `Response::json()`

Examples:

- patient messages fetch/send
- pharmacist messages fetch/send
- adherence data
- admin password verification

Short answer:

`AJAX responses are standardized through Request::expectsJson() and Response::json(), so asynchronous endpoints return clean JSON consistently.`

## 14. What coding standards are used in this project now?

Current simplified standard:

- request checks use `Request::isPost()` / `Request::isGet()`
- redirects use `Response::redirect()`
- JSON responses use `Response::json()`
- empty status responses use `Response::empty()`
- DB queries use prepared statement helpers in `Database`

This is a strong answer because it shows consistency.

## 16. Short answer for “How would you explain your codebase?”

Use this:

`This project is a custom PHP MVC-style web application without third-party frameworks. index.php acts as the front controller and routes requests to pages inside the pages folder. Each module is separated by role such as patient, pharmacist, guardian, and admin. Authentication is handled using JWT cookies, authorization is checked in role-specific head files, controllers process requests, models handle database logic through a centralized Database class using prepared statements, and shared reusable features are placed in the core folder.`

## 17. Short answer for “Why didn’t you use a framework?”

Use this:

`The project requirement was to avoid third-party frameworks and libraries, so I implemented the architecture using core PHP. To keep it maintainable, I created reusable helper classes such as Request, Response, Auth, Database, and PharmacyContext.`

## 18. Files you should remember before a viva

Memorize these:

- `index.php`
- `config/database.php`
- `core/Request.php`
- `core/Response.php`
- `core/Auth.php`
- `core/PharmacyContext.php`
- `core/ChatMessageSupport.php`
- one patient controller/model pair
- one pharmacist controller/model pair

## 19. Very short last-minute revision version

If you only remember one summary, remember this:

`index.php routes the request, Auth protects the page, Request reads input, controllers handle flow, models talk to the database through prepared statements, Response returns redirects or JSON, and core helpers store shared logic like pharmacy context and chat support.`
