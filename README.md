# Country Yoghurt Inventory System

Laravel-based inventory platform for Country Yoghurt, built in milestone iterations.

## Stack
- Backend: Laravel 13 (PHP)
- Frontend: Blade templates + plain CSS + vanilla JavaScript
- Database: SQLite (default for local setup)

## Current Scope (Milestone 1)
- Desktop-first dashboard foundation
- Sidebar navigation for Admin, Staff, Customer, Transactions, Orders, and Deliveries
- Warm brand palette with card-based layout

## Run Locally
1. Install dependencies (already done during bootstrap):

```bash
composer install
```

2. Ensure environment and app key are ready:

```bash
copy .env.example .env
php artisan key:generate
```

3. Run migrations:

```bash
php artisan migrate
```

4. Start development server:

```bash
php artisan serve
```

Open the app at http://127.0.0.1:8000

## Important Paths
- Route definition: routes/web.php
- Dashboard view: resources/views/dashboard.blade.php
- Dashboard CSS: public/assets/css/dashboard.css
- Dashboard JS: public/assets/js/dashboard.js
- Milestone notes: docs/milestones.md
- Prototype backup: prototype_backup/

## Next Milestone
Milestone 2: Define database entities and APIs for inventory entries, transactions, orders, deliveries, outlets, and role-based users.
