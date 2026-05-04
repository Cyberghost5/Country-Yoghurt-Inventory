# Country Yoghurt — Platform Documentation

> **Version:** 1.0 (as of May 2026)
> **Framework:** Laravel 13 (PHP), Blade templates, vanilla CSS
> **Database:** MySQL (via Laragon)

---

## Table of Contents

1. [Overview](#1-overview)
2. [Technology Stack](#2-technology-stack)
3. [Database Schema](#3-database-schema)
4. [User Roles & Access Control](#4-user-roles--access-control)
5. [Authentication](#5-authentication)
6. [Dashboard](#6-dashboard)
7. [Inventory Management](#7-inventory-management)
8. [Orders](#8-orders)
9. [Payments](#9-payments)
10. [Deliveries](#10-deliveries)
11. [Reports](#11-reports)
12. [Transactions](#12-transactions)
13. [Notifications](#13-notifications)
14. [User Management](#14-user-management)
15. [Admin Impersonation](#15-admin-impersonation)
16. [SMS Integration](#16-sms-integration)
17. [Print Feature](#17-print-feature)
18. [UI & Design System](#18-ui--design-system)
19. [Route Reference](#19-route-reference)

---

## 1. Overview

Country Yoghurt is a web-based inventory and operations management system built for a yoghurt distribution business. It tracks products, manages customer orders, records payments, schedules and approves deliveries, and provides real-time reporting — all behind a role-based access system. The platform serves three types of users: **Admins** who oversee the entire business, **Staff** who manage operations within their assigned state, and **Customers** who place orders and submit payments.

---

## 2. Technology Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 |
| Language | PHP 8.2+ |
| Templating | Blade (no Tailwind; plain CSS) |
| Database | MySQL |
| Local Dev | Laragon |
| CSS | Custom `public/assets/css/dashboard.css` |
| Icons | Bootstrap Icons 1.11.3 (CDN) |
| Fonts | Poppins (Google Fonts CDN) |
| HTTP Client | Guzzle (via Laravel `Http` facade) |
| SMS | BulkSMSNigeria API v2 |
| Notifications | Laravel database + mail channels |
| Auth | Laravel built-in (`Auth::attempt`) |
| Password Reset | Laravel `Password::sendResetLink` |
| File Storage | Laravel `Storage` / `public` disk |

---

## 3. Database Schema

### `users`
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| name | string | Full name |
| email | string | Unique |
| phone | string | Nigerian format |
| role | string | `admin`, `staff`, `customer` |
| state | string | Nigerian state |
| lga | string | Local Government Area |
| shop_name | string | Customers only |
| address | string | Customers only — used for delivery address auto-fill |
| password | string | Bcrypt hashed |
| remember_token | string | |
| email_verified_at | timestamp | |
| created_at / updated_at | timestamp | |

### `products`
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| name | string | |
| sku | string | Auto-generated if not provided |
| category | string | `yoghurt`, `accessories`, `packaging`, `others` |
| flavor | string | Nullable |
| size_label | string | Nullable |
| unit | string | `carton`, `pack`, `piece`, `litre` |
| cost_price | decimal(10,2) | |
| selling_price | decimal(10,2) | |
| quantity | integer | Current stock level |
| reorder_level | integer | Triggers low-stock alert |
| supplier_name | string | Nullable |
| notes | text | Nullable |
| created_by | bigint | FK → users |

### `orders`
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| order_number | string | e.g. `ORD-00042` |
| user_id | bigint | FK → users (the customer) |
| status | string | `pending`, `approved`, `rejected`, `delivered` |
| notes | text | Nullable |
| total_amount | decimal(12,2) | Sum of order items |
| approved_by | bigint | FK → users (admin who approved/rejected) |
| approved_at | timestamp | Nullable |
| rejection_reason | text | Nullable |

### `order_items`
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| order_id | bigint | FK → orders |
| product_id | bigint | FK → products (nullable on delete) |
| product_name | string | Snapshot at time of order |
| unit_price | decimal(10,2) | Snapshot |
| quantity | integer | |
| subtotal | decimal(10,2) | |

### `payments`
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| payment_number | string | e.g. `PMT-00011` |
| order_id | bigint | Nullable FK → orders |
| user_id | bigint | FK → users (who submitted) |
| amount | decimal(12,2) | |
| payment_method | string | `bank_transfer`, `cash`, `pos`, `mobile_money` |
| reference | string | Nullable (bank/transaction ref) |
| proof_path | string | Nullable — stored in `public` disk |
| notes | text | Nullable |
| reason | text | Required if no order linked |
| status | string | `pending`, `approved`, `rejected` |
| reviewed_by | bigint | FK → users (admin) |
| reviewed_at | timestamp | |
| rejection_reason | text | Nullable |

### `deliveries`
| Column | Type | Notes |
|---|---|---|
| id | bigint | PK |
| order_id | bigint | FK → orders |
| staff_id | bigint | FK → users (staff who scheduled) |
| delivery_address | text | Pre-filled from customer address |
| scheduled_at | date | Required — must be today or future |
| notes | text | Nullable |
| status | string | `pending`, `approved`, `delivered`, `rejected` |
| rejection_reason | text | Nullable |
| approved_by | bigint | FK → users (admin) |
| approved_at | timestamp | |
| delivered_at | timestamp | |

### `notifications`
Standard Laravel database notifications table — stores JSON payload per user with `read_at` timestamp.

---

## 4. User Roles & Access Control

### Admin
- Full access to all data across all states.
- Can create, edit, and view all users (staff, admin, customers).
- Approves/rejects orders, payments, and deliveries.
- Manages inventory (add, edit, adjust stock, delete products).
- Accesses the Reports page with full date-range analytics.
- Can impersonate any other user to see their view.

### Staff
- Scoped to their assigned **state only**.
- Can view orders and payments from customers in their state.
- Can create deliveries for orders in their state.
- Can view and manage deliveries for their state.
- Can create new customers.
- Can view inventory (read-only).
- Cannot approve/reject orders or payments.
- Cannot access the Reports page.

### Customer
- Can only see their own orders, payments, and deliveries.
- Can place new orders.
- Can submit payments against their approved/delivered orders.
- Cannot see other customers, staff, or any admin data.
- Cannot create deliveries or manage inventory.

---

## 5. Authentication

### Login
- **Route:** `GET / POST /login`
- Email + password authentication via `Auth::attempt()`.
- "Remember me" checkbox supported.
- On success: redirected to `/dashboard`.
- Already-authenticated users visiting `/` are redirected to `/dashboard`.

### Password Reset
- **Forgot Password:** `GET /forgot-password` — enter registered email.
- **Reset Link:** Sent via Laravel mail using `Password::sendResetLink()`.
- **Reset Form:** `GET /reset-password/{token}` — enter new password (confirmed).
- **Update:** `POST /reset-password` — validates token and updates password.

### Logout
- **Route:** `POST /logout`
- Invalidates session and regenerates CSRF token.

---

## 6. Dashboard

**Route:** `GET /dashboard`

The dashboard is fully role-aware and shows different KPIs and widgets per user.

### Admin Dashboard
- **Date filter bar** with 12 preset options (see [Date Range Options](#date-range-options)) — all transactional KPIs are date-filtered.
- **KPI cards:** Total Orders, Pending Orders, Total Payments, Pending Payments, Total Revenue, Outstanding Debt, Total Deliveries, Pending Deliveries, Completed Deliveries.
- **Headcount cards:** Staff Count, Customer Count, Active States, Products in Inventory, Low Stock, Out of Stock.
- **Recent Staff table:** Last 5 registered staff members.
- **Recent Customers table:** Last 5 registered customers.

### Staff Dashboard
- **My State KPIs:** Orders from their state, pending orders, their own deliveries (total / pending / active).
- **Payments KPIs:** State payment count, pending payments, state revenue, state outstanding debt.
- **Contacts sidebar:** Other users (staff and customers) in the same state.

### Customer Dashboard
- **My Orders KPIs:** Total, pending, approved, delivered.
- **My Payments KPIs:** Total payments, total paid amount, pending payments.
- **Debt card:** Outstanding balance across approved/delivered orders.
- **Deliveries KPIs:** Total, pending, completed.
- **Contacts sidebar:** Other contacts in the same state.

### Date Range Options
The following 12 options are available on the Admin Dashboard and Reports page:

| Option | Description |
|---|---|
| All Time | No date filter |
| Today | Current calendar day |
| Yesterday | Previous calendar day |
| Last 7 Days | Rolling 7-day window |
| Last 30 Days | Rolling 30-day window |
| This Month | 1st to last day of current month |
| Last Month | Full previous month |
| This Month Last Year | Same month, prior year |
| This Year | Jan 1 to Dec 31 of current year |
| Last Year | Full previous calendar year |
| Current Financial Year | Apr 1 – Mar 31 (current FY) |
| Last Financial Year | Apr 1 – Mar 31 (previous FY) |
| Custom Range | User-specified from/to dates |

---

## 7. Inventory Management

**Route:** `GET /admin/inventory`
**Access:** Admin and Staff (Staff can view and adjust stock; only Admin can delete products)

### Features
- **Product listing** with live search (name, SKU, supplier) and filters by category and stock status.
- **KPI bar:** Total products, total units in stock, low-stock count, out-of-stock count, total inventory value (cost).
- **Stock status badges:** In Stock (green), Low Stock (orange), Out of Stock (red) — automatically computed against `reorder_level`.
- **Add Product modal:** Name, SKU (auto-generated if blank), category, flavor, size label, unit, cost price, selling price, quantity, reorder level, supplier, notes.
- **Edit Product modal:** Same fields as Add; SKU uniqueness validated excluding self.
- **Stock Adjustment modal:** Add or remove units with an optional reason note — does not delete the product.
- **Delete Product:** Admin only; removes the product record.
- **SKU auto-generation:** Pattern based on category prefix + product name slug.

### Product Fields
- **Categories:** Yoghurt, Accessories, Packaging, Others.
- **Units:** Carton, Pack, Piece, Litre.

---

## 8. Orders

**Routes:** `/orders`, `/orders/create`, `/orders/{order}`, approve/reject/deliver actions

### Placing an Order
- **Who can place:** Staff, Admin (on behalf of a customer), or Customer (their own).
- **Form:** Select customer (staff/admin view), add product rows dynamically (JS-driven), live stock check via AJAX on each row, running total auto-calculated.
- **Validation:** At least one item required; quantities must not exceed available stock; all amounts calculated server-side.
- **On submit:** Order is created with status `pending`; the admin is notified (database + email + SMS).

### Order Statuses
| Status | Meaning |
|---|---|
| `pending` | Awaiting admin approval |
| `approved` | Approved; ready for payment and delivery |
| `rejected` | Rejected by admin with optional reason |
| `delivered` | Fully delivered |

### Admin Actions (on order detail page)
- **Approve:** Sets status to `approved`; notifies the customer.
- **Reject:** Opens a modal for an optional rejection reason; sets status to `rejected`; notifies the customer.

### Order Detail Page
- Status badge, placed-by info, total amount, total paid, balance remaining.
- Notes and rejection reason cards (when applicable).
- **Items table** with unit prices, quantities, subtotals, and order total.
- **Payments section** (for approved/delivered orders): lists all payments with status badges and a "Submit Payment" button for staff/customers if balance remains.
- **Delivery section:**
  - If no delivery: "Schedule Delivery" button (links directly to delivery create form pre-filled with this order).
  - If delivery exists: shows status badge, "View Delivery" link, and relevant action buttons (Approve Delivery, Mark as Delivered) for admin.
  - If delivery was rejected: "Schedule Delivery" button reappears so a new one can be created.
- **Print button** in the topbar.

### Order Scoping
- Admin sees all orders.
- Staff sees orders from customers in their state only.
- Customer sees only their own orders.
- All lists are paginated (20 per page) and filterable by status tabs.

---

## 9. Payments

**Routes:** `/payments`, `/payments/create`, `/payments/{payment}`, approve/reject actions

### Submitting a Payment
- **Who can submit:** Customer (own orders), Staff or Admin (on behalf of a customer).
- **Link to order:** Optional — a payment can be linked to a specific order or submitted standalone with a reason text.
- **Pre-fill from Order page:** "Submit Payment" button on an approved order opens the create form with the order pre-selected and amount pre-filled with the remaining balance.
- **Fields:** Order (optional), amount, payment method, bank/transaction reference (optional), proof of payment (JPG/PNG/PDF, max 4MB), notes.
- **Payment methods:** Bank Transfer, Cash, POS, Mobile Money.
- **Validation:** Amount > 0; proof file validated by MIME type and size.
- **On submit:** Payment created with status `pending`; admin notified (database + email).

### Payment Statuses
| Status | Meaning |
|---|---|
| `pending` | Awaiting admin review |
| `approved` | Confirmed as received |
| `rejected` | Rejected with optional reason |

### Admin Actions (on payment detail page)
- **Approve:** Sets status to `approved`; notifies the customer.
- **Reject:** Opens a modal for an optional rejection reason; notifies the customer.

### Payment Detail Page
- Payment number, status, amount, method, linked order, submitted by, reference, reviewed by.
- Reason for payment (if standalone).
- Notes card.
- Rejection reason card (if rejected).
- **Proof of Payment:** Image preview (JPG/PNG) or PDF download link.
- **Print button** in the topbar.

### Payment Scoping
- Admin sees all payments.
- Staff sees payments from customers in their state.
- Customer sees only their own payments.
- Paginated (20 per page), filterable by status tabs.

---

## 10. Deliveries

**Routes:** `/deliveries`, `/deliveries/create`, `/deliveries/{delivery}`, approve/reject/deliver actions

### Scheduling a Delivery
- **Who can schedule:** Staff and Admin.
- **Pre-fill from Order page:** "Schedule Delivery" button on an approved order opens the create form with that order locked (shown as a summary card — order number, customer, state, total). The customer and order selectors are hidden and replaced with hidden inputs.
- **Delivery address auto-fill:** When a customer is selected, the delivery address field is automatically populated from the customer's saved address (address + LGA + state, comma-separated). The field remains editable.
- **Fields:** Customer selector → Order selector (AJAX-filtered to the selected customer's approved orders), delivery address, scheduled date (required; must be today or future), notes.
- **Validation:** Order must be in `approved` status; no active (pending/approved) delivery already exists for the order.
- **On submit:** Delivery created with status `pending`; the admin is notified.

### Re-scheduling After Rejection
If a delivery is rejected, the "Schedule Delivery" button reappears on both the Order detail page (admin view) and the Orders page (staff view), allowing a new delivery to be created for the same order.

### Delivery Statuses
| Status | Label Shown | Meaning |
|---|---|---|
| `pending` | Pending Approval | Awaiting admin review |
| `approved` | Out for Delivery | Admin approved; en route |
| `delivered` | Delivered | Marked as successfully delivered |
| `rejected` | Rejected | Admin rejected with optional reason |

### Admin Actions (on delivery detail page)
- **Approve:** Sets status to `approved`; notifies the staff member.
- **Reject:** Opens a modal for an optional rejection reason; sets status to `rejected`; notifies the staff member.
- **Mark as Delivered:** (when status is `approved`) Sets status to `delivered`; records `delivered_at` timestamp.

### Delivery Detail Page
- Status badge, linked order, customer, order total, scheduled by (staff name), scheduled date.
- Approved by + approval timestamp (when applicable).
- Delivered at timestamp (when applicable).
- **Delivery Address card.**
- **Rejection Reason card** (red left border, shown only when rejected and reason provided).
- Notes card.
- **Order Items summary table** (product, qty, subtotal, total).
- **Print button** in the topbar.

### Delivery Scoping
- Admin sees all deliveries.
- Staff sees deliveries for orders belonging to customers in their state.
- Customer cannot access the deliveries list directly — delivery status is visible on their order detail page.
- Paginated (20 per page), filterable by status tabs (All, Pending, Approved, Delivered, Rejected).

---

## 11. Reports

**Route:** `GET /admin/reports`
**Access:** Admin only (returns 403 for other roles)

The Reports page provides a comprehensive, date-filtered overview of business performance. The same 12 date-range presets used on the Dashboard apply here (default: This Month).

### Section 1 — Summary KPIs (6 stat cards)
- Total Orders in period
- Orders Value (sum of `total_amount`)
- Average Order Value
- Revenue Collected (approved payments)
- Outstanding Debt (unpaid balance on approved/delivered orders)
- Pending Payments (awaiting review)

### Section 2 — Orders Breakdown
- **Orders by Status bar chart:** Visual bars for Pending, Approved, Delivered, Rejected with count and percentage of total.
- **Orders by State table:** Count and total value per Nigerian state, sorted by order count.

### Section 3 — Revenue & Payments
- **Revenue by Payment Method table:** Method name, number of payments, total collected, percentage share.
- **Top 20 Outstanding Debt Orders table:** Customer name, state, order number, order value, amount paid, remaining balance — sorted by largest outstanding balance.

### Section 4 — Deliveries
- **Deliveries by Status bar chart:** Pending, Approved/Out for Delivery, Delivered, Rejected.
- **Staff Performance table:** Each staff member's name, state, number of deliveries scheduled, and delivered count.

### Section 5 — Top Performers
- **Top 10 Products by Revenue:** Product name, number of orders containing it, total units sold, total revenue generated.
- **Top 10 Customers by Order Value:** Customer name, shop name, state, order count, total order value, total paid.

### Section 6 — Recent Orders
Last 20 orders placed within the selected period, with order number, customer name, state, status badge, total amount, and a view link.

---

## 12. Transactions

**Route:** `GET /transactions`
**Access:** All authenticated users

A unified chronological feed of all orders, payments, and deliveries merged and sorted by date descending. Serves as an activity log / audit trail.

- Displays type badge (Order / Payment / Delivery), reference number, description, amount (where applicable), status badge, and date.
- Each row links to the relevant detail page.
- Scoped per role: Admin sees all; Staff sees their own; Customer sees their own.
- Mobile-friendly card layout with a responsive table view on larger screens.

---

## 13. Notifications

**Route:** `GET /notifications`

### How Notifications Work
- Built on Laravel's database + mail notification channels.
- Every significant action triggers a notification to the relevant user(s).
- A red badge on the bell icon in the sidebar and topbar shows the unread count (capped at 99+).

### Notification Triggers

| Event | Who is Notified | Channel |
|---|---|---|
| Order placed | Admin | Database + Email |
| Order approved | Customer | Database + Email |
| Order rejected | Customer | Database + Email |
| Order marked delivered | Customer | Database + Email |
| Payment submitted | Admin | Database + Email |
| Payment approved | Customer | Database + Email |
| Payment rejected | Customer | Database + Email + reason in message |
| Delivery scheduled | Admin | Database + Email |
| Delivery approved | Staff + Customer | Database + Email |
| Delivery rejected | Staff | Database + Email |
| Delivery marked delivered | Customer | Database + Email |

### Notification Actions
- **Mark single as read:** Clicking a notification marks it read and redirects to the associated record.
- **Mark all as read:** Single button marks all unread notifications as read.
- Notifications are paginated (30 per page).

---

## 14. User Management

**Access:** Admin (all); Staff (create customers only)

### Create Staff
- **Route:** `GET/POST /users/create-staff`
- Admin only.
- Fields: Name, email, phone, state (limited to states admin has access to), LGA (dynamically populated from state selection), password (confirmed).

### Create Admin
- **Route:** `GET/POST /users/create-admin`
- Admin only.
- Same fields as staff.

### Create Customer
- **Route:** `GET/POST /users/create-customer`
- Admin or Staff.
- Extra fields: Shop name, full address (used for delivery address auto-fill).
- State is restricted for staff (their own state only).

### Edit User
- **Route:** `GET /users/{user}/edit` | `PUT /users/{user}`
- Admin only.
- All profile fields editable including optional password reset.
- Customer-specific fields (shop name, address) shown/hidden based on role.
- LGA validated against selected state using the Nigeria config.

### User List Pages
| Page | Route | Who |
|---|---|---|
| All Staff | `/admin/staff` | Admin |
| All Admins | `/admin/admins` | Admin |
| All Customers (Admin) | `/admin/customers` | Admin |
| My Customers (Staff) | `/staff/customers` | Staff |
| Customer Profile | `/customers/{customer}` | Admin + Staff |

### Nigeria Geographic Config
A `config/nigeria.php` file contains a comprehensive map of all Nigerian states to their LGAs. Used in all create/edit user forms for cascading state → LGA dropdowns (JS-driven).

---

## 15. Admin Impersonation

**Routes:** `POST /users/{user}/impersonate` | `POST /impersonate/stop`
**Access:** Admin only

- Admin can log in as any other user (staff, customer, other admin) to see exactly what that user sees.
- While impersonating, a yellow sticky banner at the top of every page shows the impersonated user's name and a "Stop & Return" button.
- Only one impersonation level is supported (cannot impersonate while already impersonating).
- Admin cannot impersonate themselves.
- Stopping impersonation restores the original admin session seamlessly.

---

## 16. SMS Integration

**Service:** `App\Services\BulkSmsService`
**Provider:** BulkSMSNigeria API v2

- Configured via `config/services.php` — requires `BULKSMS_TOKEN` and optionally `BULKSMS_SENDER` in `.env`.
- Normalises Nigerian phone numbers to the `234XXXXXXXXXX` format automatically (handles `0XX`, `234XX`, or bare 10-digit formats).
- SMS notifications are sent alongside database/email notifications for key order events.
- Gracefully degrades: if the API token is not configured, a warning is logged and the rest of the operation continues normally.
- All errors are logged to `storage/logs/laravel.log` without throwing exceptions to the user.

---

## 17. Print Feature

All three main record detail pages support browser printing.

### Supported Pages
- Order detail (`/orders/{order}`)
- Payment detail (`/payments/{payment}`)
- Delivery detail (`/deliveries/{delivery}`)

### How It Works
- A **Print** button appears in the topbar of each detail page.
- Clicking it calls `window.print()`.
- A `@media print` stylesheet in `dashboard.css` activates automatically:
  - Hides: sidebar, topbar buttons, action bars, modals, flash messages.
  - Shows: a branded **print header** with the Country Yoghurt logo, company name, and printed date/time.
  - Collapses layout to full-width single column.
  - Adds proper borders to all tables.
  - Preserves status badge colours (`print-color-adjust: exact`).
  - Sets page margins to 16mm × 14mm.

---

## 18. UI & Design System

### Palette
| Token | Value | Usage |
|---|---|---|
| Primary (sidebar bg) | `#1d086c` | Sidebar, primary buttons |
| Brand yellow | `#ffd900` | Active nav links, highlights |
| Page background | Creamy off-white | Body |
| Card background | White | All cards |
| Border | `#e8e4d9` | Subtle dividers |
| Success | `#2a9d54` | Approved status |
| Warning | `#b97c10` | Pending status |
| Danger | `#c0392b` | Rejected / error states |

### Typography
- **Font:** Poppins (Google Fonts), weights 400/500/600/700.
- Base body font size: ~14–15px.
- Labels: 0.72rem uppercase, letter-spaced.

### Layout
- **Mobile-first responsive** with sidebar drawer on mobile (hamburger toggle).
- **Desktop (≥1024px):** Fixed sidebar (250px) + main content grid.
- **Tablet (≥640px):** Topbar becomes horizontal flex row; modals become centered dialogs.

### Component Classes (selected)
| Class | Purpose |
|---|---|
| `.card` | White bordered rounded container |
| `.primary-btn` | Deep navy pill button |
| `.ghost-btn` | Bordered transparent button |
| `.danger-ghost` | Red-bordered ghost button |
| `.ord-meta-grid` | Responsive grid of metadata cards |
| `.inv-modal-overlay` | Full-screen modal overlay (toggled with `.active`) |
| `.ord-status-badge` | Order status pill badge |
| `.pay-status-badge` | Payment status pill badge |
| `.dlv-status-badge` | Delivery status pill badge |
| `.lp-success` / `.lp-error` | Flash message banners |
| `.ord-tabs` | Horizontally scrollable status tab strip |
| `.rpt-bar-row` | Report bar chart row |
| `.print-header` | Branded header (hidden on screen, visible when printing) |

### Modals
- Overlay element uses `.inv-modal-overlay`; toggled open with `.active` class (not `.is-open`).
- Opening sets `document.body.style.overflow = 'hidden'`; closing restores it.
- On mobile: sheet that slides up from bottom. On desktop (≥640px): centered dialog.

---

## 19. Route Reference

| Method | URI | Controller | Name | Access |
|---|---|---|---|---|
| GET | `/` | LoginController@showLogin | `login` | Public |
| POST | `/login` | LoginController@login | `login.post` | Public |
| POST | `/logout` | LoginController@logout | `logout` | Auth |
| GET | `/forgot-password` | ForgotPasswordController@showForm | `password.request` | Public |
| POST | `/forgot-password` | ForgotPasswordController@sendLink | `password.email` | Public |
| GET | `/reset-password/{token}` | ResetPasswordController@showForm | `password.reset` | Public |
| POST | `/reset-password` | ResetPasswordController@reset | `password.update` | Public |
| GET | `/dashboard` | DashboardController@index | `dashboard` | Auth |
| GET | `/admin/reports` | ReportController@index | `admin.reports.index` | Admin |
| GET | `/admin/staff` | AdminPagesController@staffIndex | `admin.staff.index` | Admin |
| GET | `/admin/admins` | AdminPagesController@adminIndex | `admin.admins.index` | Admin |
| GET | `/admin/customers` | AdminPagesController@customerIndex | `admin.customers.index` | Admin |
| GET | `/staff/customers` | AdminPagesController@staffCustomerIndex | `staff.customers.index` | Staff |
| GET | `/customers/{customer}` | AdminPagesController@customerShow | `customers.show` | Admin, Staff |
| GET | `/admin/inventory` | InventoryController@index | `admin.inventory.index` | Admin, Staff |
| POST | `/admin/inventory` | InventoryController@store | `admin.inventory.store` | Admin, Staff |
| PUT | `/admin/inventory/{product}` | InventoryController@update | `admin.inventory.update` | Admin, Staff |
| POST | `/admin/inventory/{product}/adjust` | InventoryController@adjustStock | `admin.inventory.adjust` | Admin, Staff |
| DELETE | `/admin/inventory/{product}` | InventoryController@destroy | `admin.inventory.destroy` | Admin |
| GET | `/orders` | OrderController@index | `orders.index` | Auth |
| GET | `/orders/create` | OrderController@create | `orders.create` | Auth |
| GET | `/orders/stock-check` | OrderController@stockCheck | `orders.stockCheck` | Auth |
| POST | `/orders` | OrderController@store | `orders.store` | Auth |
| GET | `/orders/{order}` | OrderController@show | `orders.show` | Auth |
| POST | `/orders/{order}/approve` | OrderController@approve | `orders.approve` | Admin |
| POST | `/orders/{order}/reject` | OrderController@reject | `orders.reject` | Admin |
| POST | `/orders/{order}/deliver` | OrderController@deliver | `orders.deliver` | Admin |
| GET | `/payments` | PaymentController@index | `payments.index` | Auth |
| GET | `/payments/create` | PaymentController@create | `payments.create` | Auth |
| POST | `/payments` | PaymentController@store | `payments.store` | Auth |
| GET | `/payments/{payment}` | PaymentController@show | `payments.show` | Auth |
| POST | `/payments/{payment}/approve` | PaymentController@approve | `payments.approve` | Admin |
| POST | `/payments/{payment}/reject` | PaymentController@reject | `payments.reject` | Admin |
| GET | `/deliveries` | DeliveryController@index | `deliveries.index` | Admin, Staff |
| GET | `/deliveries/create` | DeliveryController@create | `deliveries.create` | Admin, Staff |
| POST | `/deliveries` | DeliveryController@store | `deliveries.store` | Admin, Staff |
| GET | `/deliveries/{delivery}` | DeliveryController@show | `deliveries.show` | Auth |
| POST | `/deliveries/{delivery}/approve` | DeliveryController@approve | `deliveries.approve` | Admin |
| POST | `/deliveries/{delivery}/reject` | DeliveryController@reject | `deliveries.reject` | Admin |
| POST | `/deliveries/{delivery}/deliver` | DeliveryController@markDelivered | `deliveries.deliver` | Admin |
| GET | `/transactions` | TransactionController@index | `transactions.index` | Auth |
| GET | `/notifications` | NotificationController@index | `notifications.index` | Auth |
| POST | `/notifications/{id}/read` | NotificationController@markRead | `notifications.read` | Auth |
| POST | `/notifications/read-all` | NotificationController@markAllRead | `notifications.readAll` | Auth |
| GET | `/users/create-staff` | UserManagementController@createStaff | `users.create.staff` | Admin |
| POST | `/users/staff` | UserManagementController@storeStaff | `users.store.staff` | Admin |
| GET | `/users/create-admin` | UserManagementController@createAdmin | `users.create.admin` | Admin |
| POST | `/users/admin` | UserManagementController@storeAdmin | `users.store.admin` | Admin |
| GET | `/users/create-customer` | UserManagementController@createCustomer | `users.create.customer` | Admin, Staff |
| POST | `/users/customer` | UserManagementController@storeCustomer | `users.store.customer` | Admin, Staff |
| GET | `/users/{user}/edit` | UserManagementController@editUser | `users.edit` | Admin |
| PUT | `/users/{user}` | UserManagementController@updateUser | `users.update` | Admin |
| POST | `/users/{user}/impersonate` | UserManagementController@impersonate | `users.impersonate` | Admin |
| POST | `/impersonate/stop` | UserManagementController@stopImpersonating | `impersonate.stop` | Admin |
| GET | `/ajax/customers` | UserManagementController@ajaxCustomers | `ajax.customers` | Auth |
| GET | `/ajax/customer-orders` | OrderController@ajaxCustomerOrders | `ajax.customerOrders` | Auth |

---

*Documentation generated May 2026. Reflects the codebase as of the final milestone iteration.*
