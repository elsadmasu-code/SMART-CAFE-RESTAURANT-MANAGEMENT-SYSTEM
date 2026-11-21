# SMART CAFE & RESTAURANT MANAGEMENT SYSTEM (SCRMS)
## Fkr Cafe (ፍቅር ካፌ) — Kombolcha, Amhara, Ethiopia

[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![Backend: Laravel 11](https://img.shields.io/badge/Backend-Laravel%2011%20%7C%20PHP%208.4-red.svg)](https://laravel.com)
[![Frontend: Vue 3](https://img.shields.io/badge/Frontend-Vue%203%20%7C%20Tailwind%20CSS-emerald.svg)](https://vuejs.org)
[![Mobile: Flutter](https://img.shields.io/badge/Mobile-Flutter%20%7C%20Dart-blue.svg)](https://flutter.dev)
[![AI: Google Gemini](https://img.shields.io/badge/AI-Google%20Gemini%20API-orange.svg)](https://ai.google.dev)
[![Languages: Amharic & English](https://img.shields.io/badge/Languages-Amharic%20%28አማርኛ%29%20%26%20English-green.svg)](#)

---

## 1. Executive Summary

The **Smart Cafe & Restaurant Management System (SCRMS)** is an integrated enterprise hospitality platform engineered specifically for **Fkr Cafe** in **Kombolcha, Ethiopia**. It automates operations across front-of-house customer ordering, cashier billing, kitchen preparation (KDS), waiter service dispatch, recipe-based inventory depletion, local Wi-Fi geofenced employee attendance, monthly Ethiopian payroll, and financial audits.

### Key Architectural Strengths
- **No Mandatory Apps or QR Locks:** Customers can order through direct web domain (`www.fkrcafe.com`), Google Search discovery (`Fkr Cafe Kombolcha`), table QR codes, or the Android mobile app. Guest checkout requires zero registration.
- **Hybrid Local-Edge & Cloud Resilience:** In the event of regional or national internet outages, the in-cafe local edge server (`192.168.1.0/24`) continues real-time operations without disruption. Transactions are queued with UUIDv7 timestamps and synchronized when the WAN connection returns.
- **Ethiopian Payment Integration:** Built-in verification workflows for **Telebirr (ቴሌብር)**, **Commercial Bank of Ethiopia (CBE Birr)**, and cash handling with 80mm thermal receipt generation.
- **Bilingual Interface:** Complete native support for **Amharic (አማርኛ)** with Ethiopic script typography and English.
- **Google Gemini AI Assistant:** Embedded contextually for Customers (meal suggestions & fasting menus), Cashiers (reconciliation), Kitchen Chefs (preparation sequencing), Storekeepers (replenishment forecasting), and General Managers (executive performance summaries).

---

## 2. System Architecture

```text
                                CUSTOMERS
                                    |
      +-----------------------------+-----------------------------+
      |                             |                             |
Direct Web Domain             Table QR Code                 Android App
(www.fkrcafe.com)         (Encrypted Token)              (Offline SQLite)
      |                             |                             |
      +-----------------------------+-----------------------------+
                                    |
                       Laravel 11 REST API Engine
                          (/api/v1 Base Route)
                                    |
      +-----------------------------+-----------------------------+
      |                             |                             |
Token Auth & RBAC             Business Domains             Google Gemini AI
 (Sanctum Tokens)          (Orders, KDS, Finance)     (Role Contexts / AM & EN)
                                    |
                    MySQL 8.0 Primary Relational Engine
                     (50+ Tables, Indexed & Audited)
                                    |
      +-----------------------------+-----------------------------+
      |                             |                             |
 Kitchen Display (KDS)         Cashier POS              Waiter Floor Handset
(Preparation Milestones)    (Telebirr / CBE Birr)      (Table Delivery & Calls)
                                    |
                    Hybrid Edge-Cloud Synchronization
                     (Event Sourced & Idempotent)
```

---

## 3. Repository Directory Structure

```text
SMART-CAFE-RESTAURANT-MANAGEMENT-SYSTEM/
│
├── backend/                         # Laravel 11 REST API Backend
│   ├── app/
│   │   ├── Models/                  # 40+ Eloquent Models with relationships
│   │   ├── Http/Controllers/API/    # 13 REST API v1 Controllers
│   │   ├── Http/Middleware/         # RoleMiddleware, CheckCafeOpen, Idempotency
│   │   └── Services/                # InventoryService, PaymentVerificationService, GeminiAiService, SyncService, AttendanceService
│   ├── config/                      # app, auth, database, cors, sanctum, services
│   ├── database/
│   │   ├── migrations/              # 9 comprehensive database migrations (50+ tables)
│   │   └── seeders/                 # FkrCafeInitialDataSeeder with Ethiopian dishes, coffee, ingredients
│   ├── routes/                      # api.php, web.php, console.php
│   ├── composer.json                # PHP dependencies (Laravel 11, Sanctum)
│   └── .env.example                 # Ethiopian cafe settings, Gemini AI key, Wi-Fi subnet
│
├── frontend/                        # Vue 3 + Tailwind CSS Web Application
│   ├── src/
│   │   ├── assets/                  # Tailwind CSS configuration and fonts
│   │   ├── components/              # AiAssistantWidget, CallWaiterModal, ReceiptModal
│   │   ├── layouts/                 # CustomerLayout, StaffLayout
│   │   ├── locales/                 # en.json (English), am.json (አማርኛ)
│   │   ├── router/                  # Vue Router configuration
│   │   ├── services/                # API client with token management
│   │   ├── stores/                  # Pinia stores (auth, cart, menu, order, locale)
│   │   └── views/                   # Home, Menu, Cart, Checkout, OrderTracking, Dashboard, Cashier, Kitchen, Waiter, Inventory, Attendance, Finance
│   ├── package.json                 # Node dependencies
│   ├── vite.config.js               # Vite build configuration
│   └── tailwind.config.js           # Ethiopian coffee & brand color scheme
│
├── mobile/                          # Flutter Mobile Application
│   ├── lib/
│   │   ├── models/                  # Product, CartItem, OrderSummary
│   │   ├── providers/               # CartProvider, LocaleProvider
│   │   ├── screens/                 # HomeScreen, MenuScreen, CartScreen, OrderTrackingScreen, AiAssistantScreen
│   │   ├── services/                # ApiService, LocalDatabaseService (SQLite offline cache)
│   │   └── main.dart                # Entry point with Amharic & English localization
│   └── pubspec.yaml                 # Flutter dependencies
│
├── documentation/                   # Complete Specifications & Architecture
│   ├── SRS.md                       # Comprehensive Software Requirements Specification
│   ├── database-design.md           # Entity relationship schema for all 50+ tables
│   ├── architecture.md              # Hybrid edge-cloud & network design
│   ├── api-documentation.md         # Full REST API v1 endpoints & payloads
│   ├── user-manual.md               # Bilingual (AM/EN) operations guide for all 7 roles
│   └── testing-plan.md              # Functional, security & offline test plans
│
├── docker-compose.yml               # One-command containerized local edge deployment
├── LICENSE                          # GNU General Public License v3
└── README.md                        # Master Project Documentation
```

---

## 4. Quickstart Installation Guide

### Prerequisites
- **PHP 8.2+** or **PHP 8.4**
- **Composer 2.x**
- **Node.js 20+** / **24+**
- **MySQL 8.0+**
- **Flutter 3.x** (for mobile builds)

### 4.1 Backend Setup (Laravel REST API)
```bash
cd backend

# 1. Install PHP dependencies
composer install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Configure your MySQL database in .env (e.g. scrms_fkr_cafe)

# 4. Run database migrations and realistic Ethiopian seed data
php artisan migrate --seed

# 5. Start the backend development server
php artisan serve --port=8000
```
Backend API will be live at `http://localhost:8000/api/v1`.

### 4.2 Frontend Setup (Vue 3 + Tailwind CSS)
```bash
cd frontend

# 1. Install dependencies
npm install

# 2. Run the frontend development server
npm run dev
```
Web application will be live at `http://localhost:3000`.

### 4.3 Mobile App Setup (Flutter)
```bash
cd mobile

# 1. Fetch Flutter packages
flutter pub get

# 2. Run on connected Android device or emulator
flutter run
```

### 4.4 Docker Compose Setup (Single Command)
```bash
docker-compose up -d
```

---

## 5. Default Staff & Testing Credentials

| Role | Email / Phone | Password | Capabilities |
|---|---|---|---|
| **Manager** | `manager@fkrcafe.com` | `password123` | Full dashboard, inventory, payroll, finance, AI insights |
| **Cashier** | `cashier@fkrcafe.com` | `password123` | Incoming orders, Telebirr/CBE Birr verification, POS receipt printing |
| **Chef** | `chef@fkrcafe.com` | `password123` | Kitchen Display System (KDS), preparation timers, ready mark |
| **Waiter** | `waiter@fkrcafe.com` | `password123` | Table delivery, customer table call alerts (water, bill, call) |
| **Storekeeper** | `store@fkrcafe.com` | `password123` | Raw ingredients, purchase recording, wastage ledger |

---

## 6. Ethiopian Digital Payments Guide

### Telebirr (ቴሌብር)
- Customer selects Telebirr at checkout.
- Merchant Code: `0911000001` (Fkr Cafe).
- Customer transfers funds and inputs their 10-digit transaction ID.
- Cashier verifies the transaction on the POS screen before forwarding the ticket to the kitchen.

### Commercial Bank of Ethiopia (CBE Birr)
- Customer selects CBE Birr at checkout.
- Account Number: `1000123456789` or Shortcode `8899`.
- Customer enters bank confirmation reference.
- Verified in cash register reconciliation.

---

## 7. License & Credits

Developed with pride for **Fkr Cafe** in **Kombolcha, Amhara, Ethiopia**.  
Licensed under the [GNU General Public License v3.0](LICENSE).
