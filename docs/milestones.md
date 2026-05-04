# Country Yoghurt Inventory - Milestone Plan

## Vision
A reliable inventory platform for Country Yoghurt distribution across Northern Nigeria, with clear records from stock entry to last-mile delivery.

## Milestone 1 - Product Design Foundation
Status: Completed in this setup

Scope:
- dashboard information architecture
- module-level navigation (admin, staff, customer)
- high-level transaction/order/delivery surfaces

Deliverables:
- dashboard prototype files: `index.html`, `styles.css`, `app.js`
- styling guide embedded in CSS custom properties

Acceptance Criteria:
- desktop-first card dashboard is functional
- navigation switches module context
- visual palette follows warm cream/gold/brown with deep action color

## Milestone 2 - Data and Backend Blueprint
Scope:
- define entities and relationships
- write REST API endpoints and request/response schema
- use Laravel backend with Blade-based UI integration

Proposed Entities:
- users (roles: admin, staff, customer)
- inventory_items
- stock_entries
- transactions
- orders
- deliveries
- outlets

Acceptance Criteria:
- ERD approved
- API contract documented
- migration scripts prepared

## Milestone 3 - Authentication and Roles
Scope:
- secure login/logout
- role-based dashboard access and route protection
- audit fields on critical actions

Acceptance Criteria:
- each role only sees allowed actions
- action trail captures who did what and when

## Milestone 4 - Inventory and Transaction Engine
Scope:
- stock entry form and batch registration
- transaction ledger and balance updates
- low-stock alerts and threshold settings

Acceptance Criteria:
- stock levels update accurately per transaction
- low-stock rules trigger notifications

## Milestone 5 - Orders and Delivery Workflow
Scope:
- customer order placement
- staff dispatch assignment
- proof-of-delivery updates and status tracking

Acceptance Criteria:
- full order lifecycle traceable
- delivery statuses update in real time or near real time

## Milestone 6 - Reporting and Stabilization
Scope:
- dashboard analytics and export
- reconciliation tools
- testing, security review, and deployment checklist

Acceptance Criteria:
- core reports available per role
- zero critical defects in UAT checklist
