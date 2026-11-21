# SMART CAFE & RESTAURANT MANAGEMENT SYSTEM (SCRMS)
## Software Requirements Specification (SRS) - Fkr Cafe (Kombolcha, Ethiopia)

**Document Version:** 1.0  
**Target Enterprise:** Fkr Cafe, Kombolcha, Amhara Region, Ethiopia  
**System Type:** Hybrid Local-Network & Cloud Digital Management Platform  
**Supported Languages:** Amharic (አማርኛ) and English  
**Platforms:** Customer Web Application, Management Web Portal, Android Mobile App (Flutter), Local Offline Edge Server  

---

## 1. Executive Summary & Problem Statement

Fkr Cafe is a prominent hospitality establishment located in Kombolcha, Ethiopia. Prior to this system, cafe operations relied upon manual paper tickets, oral communication between waitstaff and kitchen, manual paper cash reconciliation, and physical stock card tracking. These legacy practices led to order fulfillment delays, communication discrepancies between front-of-house and kitchen staff, untracked inventory spoilage, lack of payment traceability for digital wallets (Telebirr and CBE Birr), and insufficient visibility into daily profitability.

The Smart Cafe & Restaurant Management System (SCRMS) solves these challenges through an integrated digital platform that bridges:
1. Multi-channel customer access (Direct Web Domain, Google Search Business Listing, Table QR Codes, Android Application, Shared Social Links, and Optional Table NFC).
2. Automated kitchen routing via a Kitchen Display System (KDS).
3. Waiter notification and table-call dispatching.
4. Integrated Ethiopian digital payment flows (Telebirr, CBE Birr, Cash, and Chapa gateway).
5. Recipe-based automated ingredient deduction and storekeeper management.
6. Local Wi-Fi network-restricted biometric/device employee attendance and payroll calculation.
7. Google Gemini AI assistants embedded contextually for Customers, Cashiers, Kitchen Staff, Waiters, Storekeepers, and General Managers.
8. Hybrid Local-Cloud resilience ensuring full operational continuity during national or regional internet blackouts.

---

## 2. System Architecture & Hybrid Offline-Online Protocol

### 2.1 Two-Tier Network Topology
The SCRMS infrastructure employs a high-availability hybrid architecture:
- **Local Edge Server (In-Cafe):** An on-premise industrial mini-PC or workstation running Ubuntu Server/Windows with PHP 8.4, MySQL 8.0, and Laravel Reverb on the cafe's local area network (`192.168.1.0/24`). It processes table orders, KDS ticket updates, cashier payments, waiter alerts, and local attendance even during complete external fiber or cellular outages.
- **Cloud Central Server:** A secure cloud droplet hosting the public website (`www.fkrcafe.com`), cloud API gateway, public Google search landing pages, remote management analytics, and Gemini AI integration.
- **Bi-Directional Sync Service:** Implements an event-sourced synchronization log utilizing UUIDv7/v4 primary keys, idempotency tokens, and vector timestamps. When internet connectivity is restored, local edge transactions are seamlessly synced to the cloud without race conditions or duplicate stock deductions.

### 2.2 Access Channel Consistency
Every customer access touchpoint interacts with the canonical REST API (`/api/v1`):
- **Web Domain:** High performance Vue 3 Single Page Application with PWA service workers.
- **Google Search Listing:** SEO-optimized structured schema (`Restaurant`, `LocalBusiness`, `Menu`) linked to Google Business Profile for Kombolcha coordinates.
- **QR Codes:** Cryptographically signed table tokens (`/menu?table=4&token=...`) preventing table spoofing.
- **Android App:** Native Flutter application caching the offline menu in SQLite and relaying orders via local Wi-Fi socket fallback if the WAN gateway fails.

---

## 3. User Roles and Security Matrix (RBAC)

The system enforces strict server-side Role-Based Access Control:

| Role | Permissions & Boundaries |
|---|---|
| **Manager / Admin** | Unrestricted access across all branches, financial audits, menu pricing, recipe costs, employee payroll, attendance overrides, AI business summaries, and system settings. |
| **Business Owner** | Read-only executive financial dashboards, daily/weekly/monthly profit & loss statements, investment returns, and high-level inventory audit summaries. |
| **Cashier** | View incoming customer orders, verify Telebirr/CBE Birr transaction references, accept cash, open/close register cash drawers, print fiscal receipts, and route confirmed orders to KDS. |
| **Chef / Kitchen Staff** | Dedicated KDS screen, view confirmed tickets by station (Coffee, Fast Food, Traditional, Grill), adjust status (Accepted, Preparing, Ready), report ingredient shortages. |
| **Waiter** | View assigned dining tables, receive audio/visual alerts when tickets are marked 'Ready' in KDS, confirm delivery to table, respond to customer service call alerts. |
| **Storekeeper** | Manage raw ingredients, record received supplier purchases, track stock movements, log spoilage/wastage with reason codes, trigger manual stock counts. |
| **Customer (Guest / Registered)** | Browse bilingual menu, customize item options (sizes, add-ons), submit dine-in/takeaway/delivery orders, request waiter service, chat with Customer AI. |

---

## 4. Detailed Functional Modules

### 4.1 Bilingual Menu & Product Master
- Support for Amharic (አማርኛ) and English with Ethiopic script font support.
- Multi-dimensional categorisation (Coffee & Tea, Traditional Ethiopian Dishes, Pizzas, Burgers, Fresh Juices, Pastries).
- Variant groups (e.g., Cup Size: Single/Double, Crust: Thin/Thick) and Add-on modifiers (Extra Cheese, Avocado Slice, Chili Paste).
- Preparation time estimates and allergen tags (Gluten, Dairy, Peanuts, Spicy/ሚስማር).

### 4.2 Order Management Lifecycle
The state machine strictly transitions:
`Draft` $\rightarrow$ `Submitted` $\rightarrow$ `Awaiting Payment` $\rightarrow$ `Confirmed` $\rightarrow$ `Accepted by Kitchen` $\rightarrow$ `Preparing` $\rightarrow$ `Ready` $\rightarrow$ `Assigned to Waiter` $\rightarrow$ `Delivered` $\rightarrow$ `Completed`.
Cancellations require authorized manager approval and generate reverse stock adjustment entries.

### 4.3 Cashier & Ethiopian Payment Integration
- **Cash:** Manual register session tracking with opening float, in-drawer cash tally, and variance reporting at shift close.
- **Telebirr:** Direct verification of 10-character transaction reference IDs via USSD/SMS webhook or cashier dual-check.
- **CBE Birr:** Commercial Bank of Ethiopia digital wallet confirmation with reference validation.
- **Receipts:** Digital receipt viewer and 80mm ESC/POS thermal printer integration containing Cafe TIN, receipt number, order items, VAT/service breakdown, and payment method.

### 4.4 Kitchen Display System (KDS)
- Real-time WebSockets feed (Laravel Reverb) with acoustic notification for new tickets.
- Dynamic color-coding based on target preparation time (Green $<10$ min, Orange $10-20$ min, Red $>20$ min delay).
- Multi-station splitting: Barista screen for coffee/beverages, Hot Kitchen for food.

### 4.5 Automated Inventory & Recipe Engine
- Bill of Materials (BOM) linked to each menu product.
- Automatic stock deduction executed at the `Confirmed` state within a database transaction with `PESSIMISTIC_WRITE` locks.
- Idempotency guards prevent duplicate deduction on network retries.
- Wastage logging with financial loss calculations.

### 4.6 Employee, Local Wi-Fi Attendance & Payroll
- Network-bound clock-in/clock-out: Requests must originate from the cafe's registered Wi-Fi BSSID and local IP subnet (`192.168.1.0/24`). Outside IP requests are rejected.
- Shift scheduling with automated tardiness and early departure detection.
- Payroll generation with base salary, overtime computation, tax deductions (Ethiopian income tax brackets), and net pay calculation.

### 4.7 Contextual Google Gemini AI Integration
- **Customer AI:** Recommends traditional dishes and coffee pairings in Amharic or English, answers dietary inquiries without hallucinating unlisted items.
- **Cashier AI:** Reconciles order discrepancies and summarizes shift totals.
- **Chef AI:** Suggests preparation sequencing during peak rush hours.
- **Storekeeper AI:** Analyzes historical consumption trends and forecasts next-day ingredient replenishment orders.
- **Manager AI:** Delivers conversational executive reports (e.g., *"What were my top 3 most profitable breakfast items this week in Kombolcha?"*).

---

## 5. Non-Functional & Regulatory Standards
- **Response Time:** API p95 latency under 150ms on local LAN; under 600ms on cloud WAN.
- **Security:** Argon2id password hashing, Sanctum token authentication, strictly validated input payloads, parameterized SQL, CSRF and CORS protection.
- **Currency:** Ethiopian Birr (ETB / ብር) stored as `DECIMAL(12, 2)` to eliminate floating-point rounding errors.
