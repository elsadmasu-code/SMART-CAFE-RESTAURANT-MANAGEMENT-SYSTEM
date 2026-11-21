# SMART CAFE & RESTAURANT MANAGEMENT SYSTEM (SCRMS)
## Comprehensive Database Architecture & Schema Specification

**Target Database:** MySQL 8.0+ / MariaDB 10.11+ / PostgreSQL 16+  
**Collation:** `utf8mb4_unicode_ci` (Full Amharic Ge'ez & Emoji support)  
**Primary Key Convention:** Auto-incrementing BigInt for local relational tables; UUIDv4/UUIDv7 for distributed and offline-synchronized records.  
**Monetary Representation:** `DECIMAL(12, 2)` for Ethiopian Birr (ETB).

---

## 1. Entity Relationship Overview

```text
+------------------+         +-------------------+         +---------------------+
|     branches     | <----+--|     employees     | <-------|     attendance      |
+------------------+      |  +-------------------+         +---------------------+
         |                |            |                              |
         |                |  +-------------------+                    |
         |                +--|     shifts        |                    |
         |                   +-------------------+                    |
         v                                                            v
+------------------+         +-------------------+         +---------------------+
|  dining_tables   |         |     products      | <----+--|     recipes         |
+------------------+         +-------------------+      |  +---------------------+
         |                             |                |             |
         v                             v                |             v
+------------------+         +-------------------+      |  +---------------------+
|  table_sessions  |         | product_variants  |      |  |  recipe_items / BOM |
+------------------+         +-------------------+      |  +---------------------+
         |                             |                |             |
         v                             v                |             v
+------------------------------------------------+      |  +---------------------+
|                     orders                     |      +--|   ingredients       |
+------------------------------------------------+         +---------------------+
         |                   |                   |                    |
         v                   v                   v                    v
+------------------+ +---------------+ +-----------------+ +---------------------+
|   order_items    | |   payments    | | kitchen_tickets | |   stock_movements   |
+------------------+ +---------------+ +-----------------+ +---------------------+
```

---

## 2. Table Schemas by Domain (Sections 24.1 – 24.9)

### 2.1 Core System & Authentication Tables
- **`branches`**: Cafe branch metadata (code, name, address, city 'Kombolcha', phone, tax_identification_number, currency_code 'ETB', is_active).
- **`users`**: Login credentials (uuid, branch_id, name, email, phone, password_hash, status, remember_token, created_at, updated_at).
- **`roles`**: RBAC roles (`manager`, `cashier`, `chef`, `waiter`, `storekeeper`, `customer`, `business_owner`).
- **`permissions`**: Granular abilities (`orders.create`, `orders.confirm`, `payments.verify`, `inventory.manage`, `reports.view`, `payroll.calculate`).
- **`role_permissions`**: Cross-reference mapping role IDs to permission IDs.
- **`user_roles`**: Cross-reference mapping user IDs to role IDs.
- **`employees`**: Employment master record (user_id, branch_id, employee_number, department_id, position, hire_date, salary_base, emergency_phone, active_status).
- **`devices`**: Authorized POS terminals, KDS tablets, and waiter handsets with hardware UUID, friendly name, IP address, and revocation flags.
- **`system_settings`**: Key-value system configurations (opening_time, closing_time, default_vat_rate, service_charge_rate, wifi_bssid, wifi_subnet_cidr, offline_mode_enabled).
- **`audit_logs`**: Immutable security trails (user_id, branch_id, action, entity_type, entity_id, ip_address, user_agent, payload_diff, timestamp).

### 2.2 Customer & Access Tables
- **`customer_profiles`**: Registered and frequent guest profiles (phone, full_name, email, language_preference 'am'/'en', loyalty_points).
- **`customer_addresses`**: Delivery coordinates and landmarks in Kombolcha (kebelle, house_number, landmark, contact_phone).
- **`customer_preferences`**: Dietary tags (fasting/orthodox, vegetarian, no-sugar).
- **`customer_consents`**: GDPR/Ethiopian privacy compliance audit logs.
- **`dining_tables`**: Physical tables (branch_id, table_number, seating_capacity, zone 'Indoor'/'Garden'/'Balcony', is_active).
- **`qr_codes`**: Unique cryptographic QR tokens (table_id, signature_token, qr_image_url, scan_count, expires_at).
- **`table_sessions`**: Active table occupancy state (table_id, session_token, started_at, closed_at, waiter_user_id, status 'vacant'/'occupied'/'reserved').

### 2.3 Menu & Product Hierarchy
- **`categories`**: Menu groups (slug, name_en, name_am, display_order, icon, is_active).
- **`products`**: Item catalog (category_id, sku, price, preparation_time_minutes, image_url, is_available, is_featured).
- **`product_translations`**: Localized texts (product_id, locale 'en'/'am', name, description).
- **`product_variants`**: Size and preparation variations (product_id, variant_name_en, variant_name_am, price_modifier).
- **`product_addons`**: Optional add-ons (product_id, addon_name_en, addon_name_am, extra_price).
- **`allergens`**: Allergen master (code, name_en, name_am, icon).
- **`product_allergens`**: Pivot linking products with declared allergens.
- **`business_hours`**: Opening schedule per day of the week (day_of_week, open_time, close_time, is_closed).

### 2.4 Orders & Financial Transactions
- **`orders`**: Master order header (uuid, branch_id, order_number, customer_id, table_id, order_type 'dine_in'/'takeaway'/'delivery', order_status, subtotal, discount_amount, tax_amount, service_charge, total_amount, payment_status 'pending'/'paid'/'partially_paid'/'refunded', notes, created_at).
- **`order_items`**: Line items snapshotting product state at order time (order_id, product_id, product_name_snapshot, unit_price_snapshot, quantity, total_price, special_instructions).
- **`order_item_options`**: Selected variants and add-ons for line items.
- **`order_status_history`**: Audit trail of order status transitions (order_id, from_status, to_status, changed_by_user_id, reason, created_at).
- **`payments`**: Payment records (order_id, branch_id, payment_method 'cash'/'telebirr'/'cbe_birr'/'chapa', transaction_reference, amount, status 'pending'/'verified'/'failed', verified_by_user_id, verified_at).
- **`receipts`**: Official fiscal receipt records (receipt_number, order_id, cashier_id, printed_copies, qr_payload, created_at).
- **`cash_registers`**: Cashier shift sessions (cashier_user_id, opening_balance, closing_balance, calculated_cash, discrepancy, opened_at, closed_at, status).
- **`cash_transactions`**: Petty cash movements in and out of register.

### 2.5 Kitchen, Waiter & Real-Time Dispatch
- **`kitchen_stations`**: Kitchen dispatch stations (Barista/Coffee, Traditional Grill, Fast Food & Pizza).
- **`kitchen_tickets`**: Ticket headers routed to stations (order_id, station_id, status 'new'/'accepted'/'preparing'/'ready', accepted_at, ready_at, delay_notified).
- **`kitchen_ticket_items`**: Individual items assigned to a kitchen ticket.
- **`waiter_assignments`**: Assignment linking ready orders to specific floor waiters (order_id, waiter_user_id, assigned_at, delivered_at).
- **`service_calls`**: Direct table service requests (table_id, request_type 'water'/'bill'/'waiter_call'/'complaint', status 'pending'/'acknowledged'/'completed', handled_by_user_id).
- **`notifications`**: Real-time push notification queue (recipient_user_id, title, body, channel 'web'/'push'/'sms', is_read, metadata).

### 2.6 Inventory & Recipe (BOM) Management
- **`inventory_locations`**: Main Dry Store, Cold Room, Bar Stock, Kitchen Pantry.
- **`suppliers`**: Raw ingredient vendors (name, contact_person, phone, address).
- **`ingredients`**: Raw material catalog (name, unit 'kg'/'g'/'liter'/'piece', current_stock, minimum_stock_alert, cost_per_unit).
- **`recipes`**: Bill of materials header linked to products or variants.
- **`recipe_items`**: Formula ingredients (recipe_id, ingredient_id, quantity_required).
- **`purchases`**: Stock intake headers (supplier_id, invoice_number, total_cost, purchase_date, received_by_user_id).
- **`purchase_items`**: Line items of received stock.
- **`stock_movements`**: Immutable ledger of all inventory transactions (ingredient_id, movement_type 'purchase'/'order_deduction'/'wastage'/'adjustment', quantity, balance_after, reference_id, created_at).
- **`wastage_records`**: Inventory spoilage and breakage log (ingredient_id, quantity, reason, financial_loss, recorded_by_user_id).

### 2.7 Human Resources, Attendance & Payroll
- **`departments`**: Management, Service/Waiters, Barista, Kitchen, Cleaning, Security.
- **`shifts`**: Daily shift windows (Morning: 06:00-14:00, Evening: 14:00-22:00, Full Day).
- **`employee_shifts`**: Shift rosters.
- **`attendance`**: Daily biometric/network clock-ins (employee_id, shift_id, clock_in_time, clock_out_time, network_ip, device_bssid, verification_status 'verified_local'/'manual_override', is_late, is_early_departure).
- **`leave_types`**: Annual, Sick, Maternity, Emergency.
- **`leave_requests`**: Employee time-off requests with manager approval workflow.
- **`payroll_periods`**: Monthly Ethiopian calendar payroll runs.
- **`payroll_items`**: Computed employee salary slips (base, overtime_hours, overtime_pay, transport_allowance, income_tax_et, pension_et, net_pay).

### 2.8 Finance & Reporting
- **`expense_categories`**: Utilities, Rent, Municipal Taxes, Supplies, Marketing.
- **`expenses`**: Operational expenditures (category_id, amount, description, receipt_voucher_url, approved_by_user_id, expense_date).
- **`daily_reports`**: Materialized end-of-day financial snapshot for rapid executive reporting.

### 2.9 AI Engine & Offline Synchronization
- **`ai_conversations`**: Active conversational sessions tagged by role (`customer`, `manager`, `chef`, etc.).
- **`ai_messages`**: Chat history with token usage metrics, latency, and language code.
- **`sync_events`**: Event-sourced queue of mutations queued locally for cloud synchronization (event_uuid, aggregate_type, payload_json, sync_state 'pending'/'synced'/'conflict', retry_count).
- **`sync_conflicts`**: Log of concurrent writes between edge and cloud requiring administrative resolution.
- **`idempotency_records`**: Hash table guaranteeing exactly-once transaction processing for API calls.
