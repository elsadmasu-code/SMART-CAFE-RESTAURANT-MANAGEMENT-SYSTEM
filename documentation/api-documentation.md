# SMART CAFE & RESTAURANT MANAGEMENT SYSTEM (SCRMS)
## RESTful API Specification (v1)

**Base URL:** `https://api.fkrcafe.com/api/v1` or `http://192.168.1.100:8000/api/v1`  
**Authentication Header:** `Authorization: Bearer <sanctum_token>`  
**Content-Type:** `application/json`  
**Standard Response Envelope:**
```json
{
  "success": true,
  "message": "Operation completed successfully.",
  "data": {},
  "timestamp": "2026-11-21T20:20:00Z"
}
```

---

## 1. Authentication Endpoints

### 1.1 `POST /auth/login`
Authenticates staff or registered customers.
- **Request:**
  ```json
  {
    "email_or_phone": "manager@fkrcafe.com",
    "password": "SecurePassword123!",
    "device_name": "Manager POS Tablet"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "token": "1|qX8p29...",
      "user": {
        "id": 1,
        "name": "Abebe Kebede",
        "email": "manager@fkrcafe.com",
        "roles": ["manager"],
        "permissions": ["all"]
      }
    }
  }
  ```

### 1.2 `POST /auth/logout`
Revokes current active session token.

---

## 2. Menu & Product Endpoints

### 2.1 `GET /menu`
Returns the active hierarchical catalog with category groupings, items, variants, add-ons, and translations.
- **Query Parameters:** `locale=am|en`, `category_id=1`
- **Response:**
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "name": "Ethiopian Special Coffee (የኮምቦልቻ ልዩ ቡና)",
        "categories": [
          {
            "id": 1,
            "name": "Coffee & Hot Drinks (ትኩስ መጠጦች)",
            "products": [
              {
                "id": 101,
                "name": "Traditional Jebena Coffee (የጀበና ቡና)",
                "description": "Slow brewed organic Ethiopian Highland roast with Rue (ጤና አዳም).",
                "price": 40.00,
                "currency": "ETB",
                "preparation_time_minutes": 7,
                "image_url": "/images/jebena_coffee.jpg",
                "is_available": true
              }
            ]
          }
        ]
      }
    ]
  }
  ```

---

## 3. Order Management Endpoints

### 3.1 `POST /orders`
Creates a customer or guest order.
- **Headers:** `X-Idempotency-Key: 9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d`
- **Request:**
  ```json
  {
    "order_type": "dine_in",
    "table_id": 4,
    "customer_name": "Tigist Haile",
    "customer_phone": "+251911234567",
    "notes": "Spicy, extra chili on the side",
    "items": [
      {
        "product_id": 101,
        "quantity": 2,
        "variant_id": null,
        "addon_ids": [1],
        "special_instructions": "Low sugar"
      },
      {
        "product_id": 204,
        "quantity": 1,
        "variant_id": 2,
        "addon_ids": []
      }
    ]
  }
  ```
- **Response (201 Created):**
  ```json
  {
    "success": true,
    "data": {
      "order_number": "FKR-20261121-0042",
      "order_uuid": "f81d4fae-7dec-11d0-a765-00a0c91e6bf6",
      "subtotal": 280.00,
      "tax_amount": 42.00,
      "service_charge": 14.00,
      "total_amount": 336.00,
      "status": "awaiting_payment",
      "tracking_url": "/order-tracking/f81d4fae-7dec-11d0-a765-00a0c91e6bf6"
    }
  }
  ```

### 3.2 `GET /orders/{uuid}/status`
Real-time tracking of order preparation progress.

---

## 4. Payment & Cashier Endpoints

### 4.1 `POST /payments/process`
Registers or verifies a customer payment.
- **Request:**
  ```json
  {
    "order_id": "f81d4fae-7dec-11d0-a765-00a0c91e6bf6",
    "payment_method": "telebirr",
    "transaction_reference": "TB2691823746",
    "amount_paid": 336.00
  }
  ```

### 4.2 `POST /cashier/orders/{id}/confirm`
Cashier confirms verified payment and forwards ticket to KDS.

---

## 5. Kitchen Display System (KDS) Endpoints

### 5.1 `GET /kitchen/tickets`
Returns live tickets filtered by kitchen station.

### 5.2 `PATCH /kitchen/tickets/{id}/status`
Transitions preparation state (`accepted` $\rightarrow$ `preparing` $\rightarrow$ `ready`).

---

## 6. Table & Waiter Service Endpoints

### 6.1 `POST /service-calls`
Customer initiates assistance request from table.
- **Request:**
  ```json
  {
    "table_id": 4,
    "request_type": "water",
    "notes": "Cold bottled water please"
  }
  ```

### 6.2 `GET /waiter/assignments`
Waiter queries active pending table tasks and ready orders.

---

## 7. Employee & Network Attendance Endpoints

### 7.1 `POST /attendance/clock-in`
Submits employee clock-in with network validation.
- **Request:**
  ```json
  {
    "employee_id": 5,
    "device_bssid": "34:8A:12:F4:99:A1"
  }
  ```
- **Validation:** Server verifies caller IP is within `192.168.1.0/24`. If outside, returns `403 Forbidden` with `"Attendance clock-in only permitted on Fkr Cafe local network"`.

---

## 8. Artificial Intelligence Endpoints

### 8.1 `POST /ai/chat`
Contextual bilingual AI assistance using Google Gemini.
- **Request:**
  ```json
  {
    "message": "ምን አይነት የጾም ምግብ አላችሁ?",
    "locale": "am",
    "session_id": "conv-10492"
  }
  ```
- **Response:**
  ```json
  {
    "success": true,
    "data": {
      "reply": "ሰላም! በፍቅር ካፌ ዛሬ የሚገኙ ምርጥ የጾም ምግቦች፡ 1) ሽሮ ፈሰስ በተጋሚኖ 2) ልዩ አትክልት በየአይነቱ 3) የጾም ፒዛ በአትክልት እና 4) ስፕሪስ ጭማቂ ይገኙልናል። ማዘዝ የሚፈልጉትን ንገሩኝ!",
      "suggested_actions": ["view_fasting_category", "add_shiro_to_cart"]
    }
  }
  ```
