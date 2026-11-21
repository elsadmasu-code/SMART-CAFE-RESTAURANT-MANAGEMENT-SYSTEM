# SMART CAFE & RESTAURANT MANAGEMENT SYSTEM (SCRMS)
## System Architecture & Technical Infrastructure Specification

---

## 1. High-Level Architecture Overview

SCRMS is engineered with a decoupled, modular service-oriented architecture designed to operate seamlessly across intermittent network environments:

```
+-----------------------------------------------------------------------------------------+
|                                    CLIENT LAYER                                         |
|                                                                                         |
|  +--------------------+   +--------------------+   +------------------+  +-----------+  |
|  | Customer Web (PWA) |   | Management Web App |   | Flutter Android  |  | Table QR  |  |
|  | (Vue 3 + Tailwind) |   | (Vue 3 Pinia POS)  |   | (Customer/Staff) |  | / NFC Tag |  |
|  +--------------------+   +--------------------+   +------------------+  +-----------+  |
+--------------------------------------------+--------------------------------------------+
                                             |  HTTPS / WSS (REST / WebSockets)
                                             v
+-----------------------------------------------------------------------------------------+
|                                  API GATEWAY & ROUTING                                  |
|                            Laravel 11 REST API Engine (/api/v1)                         |
|   - Rate Limiting   - Sanctum Token Auth   - Role Middleware   - Idempotency Interceptor |
+--------------------------------------------+--------------------------------------------+
                                             |
                     +-----------------------+-----------------------+
                     v                                               v
+----------------------------------------+       +----------------------------------------+
|          CORE BUSINESS DOMAINS         |       |         INTEGRATION SERVICES           |
|                                        |       |                                        |
|  * Order & Billing Management          |       |  * Google Gemini AI Engine             |
|  * KDS Real-Time Dispatch (Reverb)     |       |    (Multilingual AM/EN Contexts)       |
|  * Recipe & Inventory Depletion Engine |       |  * Ethiopian Payments Gateway          |
|  * Wi-Fi Bound Attendance & HR         |       |    (Telebirr, CBE Birr, Chapa)         |
|  * Financial Auditing & Cost of Goods  |       |  * Thermal Receipt Printing (ESC/POS)  |
+----------------------------------------+       +----------------------------------------+
                     |                                               |
                     v                                               v
+----------------------------------------+       +----------------------------------------+
|             PERSISTENCE                |       |        HYBRID SYNC SUBSYSTEM           |
|  MySQL 8.0 Primary Relational Engine   | <===> |  Edge-Cloud Bi-Directional Event Sync  |
|  IndexedDB (Browser PWA Cache)         |       |  Conflict Resolution Engine            |
|  SQLite (Android Local Database)       |       |  Idempotency & Replay Protection       |
+----------------------------------------+       +----------------------------------------+
```

---

## 2. Hybrid Local-Edge & Cloud Architecture

### 2.1 The Offline Operational Challenge in Ethiopia
In regional centers such as Kombolcha, broadband and 4G connectivity may experience periodic interruptions due to fiber maintenance, power outages, or national telecommunication adjustments. SCRMS guarantees that restaurant operations (ordering, kitchen preparation, billing, cash handling, and attendance) continue without disruption.

### 2.2 Operational Modes
1. **Normal Connected Mode:**
   - Both Cloud Server (`api.fkrcafe.com`) and Local Edge Server (`192.168.1.100`) are active.
   - External customers browse the cloud website or Google Business listing.
   - Cafe customers connect via local Wi-Fi or cellular networks.
   - Sync service continuously replicates changes in real time.
2. **Local Autonomous Edge Mode (Internet Down):**
   - In-store router assigns local IP addresses.
   - In-cafe tablets, cashier PC, KDS displays, and customer Wi-Fi phones communicate directly with `http://192.168.1.100:8000`.
   - Orders are processed, ingredients deducted, receipts printed, and staff attendance validated.
   - Outgoing events are enqueued in `sync_events` table with UUIDv7 timestamps and status `pending`.
3. **Reconnection & Catch-Up Mode:**
   - The sync daemon detects internet restoration via cloud health check ping (`/api/v1/health`).
   - Batched sync events are dispatched to cloud with transactional conflict checks.
   - Cloud updates global sales records, AI aggregates insights, and cloud backups are taken.

---

## 3. Real-Time Communication Pipeline (WebSockets / Laravel Reverb)

The system utilizes WebSockets for event dispatch:
- **`kitchen-orders.{station_id}`**: Broadcasts when cashier confirms an order. KDS updates display card and plays alert chime.
- **`waiter-alerts.{branch_id}`**: Broadcasts when kitchen marks an order `Ready` or when a customer clicks `Call Waiter`.
- **`order-tracking.{order_uuid}`**: Dedicated channel for the customer's phone to receive live preparation milestones.
- **Fallback Polling:** If the client environment does not support WebSockets (e.g. restrictive cellular carrier), client automatically switches to 4-second exponential backoff REST polling without user disruption.

---

## 4. Google Gemini AI Architecture

### 4.1 System Prompts & Contextual Roles
The AI Subsystem interfaces with Google's Gemini API through a server-side proxy (`GeminiAiService.php`), ensuring API keys are never exposed to clients.

The system injects domain-specific grounding data into the prompt based on user authorization:
- **Customer AI Prompt:** Grounded solely on the active menu JSON (names, Ethiopian birr prices, allergens, Ethiopian ingredients). Prohibited from promising items outside the menu. Responses in either Amharic or English matching the user's input locale.
- **Cashier AI Prompt:** Grounded on order status flow, shift reconciliation guidance, and payment troubleshooting.
- **Chef AI Prompt:** Grounded on recipes, cooking sequence optimization, and ingredient substitutions.
- **Manager AI Prompt:** Grounded on aggregated sales KPIs, cost-of-goods-sold (COGS), employee attendance metrics, and inventory restock predictions.

---

## 5. Security & Network Boundary Design

1. **Authentication:** Laravel Sanctum issuing cryptographically signed Personal Access Tokens with custom token abilities (`role:manager`, `role:cashier`, etc.).
2. **Attendance Geofencing / Network Bound Verification:**
   - Client sends clock-in request containing client IP and device signature.
   - Server checks `REMOTE_ADDR` against the cafe's registered CIDR range (`192.168.1.0/24`). Requests from public or external cellular IPs are rejected.
3. **Audit Trails:** Critical state changes (price change, cash register adjustment, order cancellation, role elevation) produce immutable rows in `audit_logs`.
4. **Idempotency Strategy:** Every mutative API call (`POST /orders`, `POST /payments`) accepts an `X-Idempotency-Key` header. Duplicate attempts return the cached original response without reprocessing.
