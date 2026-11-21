# SMART CAFE & RESTAURANT MANAGEMENT SYSTEM (SCRMS)
## Master Quality Assurance & Testing Plan

**System:** Smart Cafe & Restaurant Management System (SCRMS) - Fkr Cafe  
**Location:** Kombolcha, Ethiopia  
**Test Coverage:** Functional, Security, Offline Resilience, Performance, and User Acceptance (UAT)

---

## 1. Test Strategy Matrix

| Test Domain | Scope | Tooling & Methodology |
|---|---|---|
| **Unit Testing** | Individual business services (InventoryService, AttendanceService, PaymentVerificationService) | PHPUnit / Pest PHP |
| **API Integration** | Full REST endpoint cycles with database rollback transactions | Laravel HTTP Tests / Postman Collection |
| **Frontend E2E** | Vue 3 Customer Cart, Ordering, KDS live transitions, Language Toggle | Playwright / Cypress / Manual Scenarios |
| **Offline Resilience** | WAN simulation, Local Edge fallback, SQLite offline storage, Sync reconciliation | Local network isolation, iptables firewall drops |
| **Security Auditing** | SQL injection, XSS, RBAC privilege escalation, Idempotency replay | OWASP ZAP, Burp Suite, automated assertions |

---

## 2. Key Test Cases

### TC-01: Multi-Channel Customer Order Submission
- **Description:** Verify customer can place an order via Web, QR code token, and guest checkout.
- **Expected Outcome:** Order created in `awaiting_payment` state; table ID locked; order items accurately priced in ETB; unique order number generated.

### TC-02: Strict Recipe Inventory Deduction
- **Description:** Order confirmed for 2x "Fkr Special Pizza". Recipe requires 0.5kg flour, 0.3kg cheese, 0.2kg tomato per pizza.
- **Expected Outcome:** Database transaction decrements Flour by 1.0kg, Cheese by 0.6kg, Tomato by 0.4kg. If inventory level falls below safety threshold, trigger `low_stock` alert.
- **Idempotency Check:** Re-submitting the same payment confirmation payload must NOT deduct inventory twice.

### TC-03: Local Wi-Fi Bound Attendance Security
- **Description:** Employee tries to clock in from home using a cellular 4G IP (`197.156.x.x`).
- **Expected Outcome:** Backend rejects request with `403 Forbidden` (`IP not authorized for attendance`).
- **Pass Criterion:** Only requests originating from the cafe's registered Wi-Fi subnet (`192.168.1.0/24`) succeed.

### TC-04: Digital Payment Verification (Telebirr & CBE Birr)
- **Description:** Customer uploads an unverified payment screenshot.
- **Expected Outcome:** System marks order as `payment_verification_pending`. Cashier must explicitly verify the 10-digit transaction ID before status moves to `confirmed` and routes to KDS.

### TC-05: Total Internet Outage & Sync Reconnection
- **Description:** Disconnect external WAN router uplink for 30 minutes while 10 orders are placed on the local network.
- **Expected Outcome:**
  1. Cashier POS, KDS screens, and local customer Wi-Fi continue running on the Local Edge server.
  2. Kitchen prepares and waiters deliver food.
  3. All transactions are logged locally with UUIDv7 in `sync_events`.
  4. Upon WAN reconnection, synchronization daemon pushes events to Cloud Server without primary key collisions or duplicate stock drops.

### TC-06: Google Gemini Multilingual AI Grounding
- **Description:** Ask customer assistant *"Do you serve raw meat kitfo today?"* in Amharic (`ዛሬ ክትፎ አለ?`).
- **Expected Outcome:** AI responds politely in Amharic based only on the current active menu. If Kitfo is not in today's menu, it states it is unavailable and recommends alternative meat dishes (e.g. Special Tibs).
