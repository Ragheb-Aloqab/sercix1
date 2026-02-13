# Database Relationships Review

## Overview Diagram

```
┌─────────────┐     ┌──────────────────┐     ┌─────────────┐
│   Company   │────▶│  CompanyBranch   │     │   Service   │
└──────┬──────┘     └────────┬─────────┘     └──────┬──────┘
       │                     │                      │
       │ hasMany             │ hasMany              │ belongsToMany
       ▼                     ▼                      │ (company_services)
┌─────────────┐     ┌─────────────┐                  │
│  Vehicle    │◀────│  Company    │◀────────────────┘
└──────┬──────┘     └──────┬──────┘
       │                   │
       │ belongsTo         │ hasMany
       │                   ▼
       │            ┌─────────────┐
       └───────────▶│    Order    │◀──────────┐
                    └──────┬──────┘           │
                           │                  │ assigned technician
                           │ hasMany          │
              ┌────────────┼────────────┐     │
              ▼            ▼            ▼     │
       ┌──────────┐ ┌──────────┐ ┌──────────┐ │
       │ Payment  │ │ Invoice  │ │Attachment│ │
       └────┬─────┘ └────┬─────┘ └──────────┘ │
            │           │                     │
            │ belongsTo │ belongsTo           │
            └───────────┴─────────────────────┘
                       │
                       ▼
              ┌─────────────────┐
              │      User       │ (admin/technician)
              └─────────────────┘
```

---

## Core Flow: Company → Orders → Payments

| From       | To        | Relationship | FK Column   | Notes                          |
|------------|-----------|--------------|-------------|--------------------------------|
| **Company**| **Order** | hasMany      | company_id  | OK                             |
| **Order**  | **Payment**| hasMany     | order_id    | OK (company_id removed)        |
| **Payment**| **Order** | belongsTo    | order_id    | Company via `$payment->order->company` |

---

## 1. Company

| Relation       | Type         | Inverse Model   | Pivot/Table   |
|----------------|--------------|-----------------|---------------|
| branches       | hasMany      | CompanyBranch   | -             |
| vehicles       | hasMany      | Vehicle         | -             |
| orders         | hasMany      | Order           | -             |
| invoices       | hasMany      | Invoice         | -             |
| services       | belongsToMany| Service         | company_services |
| otpVerifications | hasMany    | OtpVerification | -             |
| notifications | morphMany    | Notification    | (polymorphic) |

---

## 2. CompanyBranch

| Relation | Type      | Inverse Model | Notes                          |
|----------|-----------|---------------|--------------------------------|
| company  | belongsTo | Company       | company_id                     |
| vehicles | hasMany   | Vehicle       | company_branch_id              |
| ~~orders~~ | *removed* | Order       | Orders table has no `company_branch_id`. Use `company->orders()` instead. |

--- 

## 3. Vehicle

| Relation | Type      | Inverse Model | Notes      |
|----------|-----------|---------------|------------|
| company  | belongsTo | Company       | company_id |
| branch   | belongsTo | CompanyBranch | company_branch_id |
| orders   | hasMany   | Order         | vehicle_id |

---

## 4. Order

| Relation     | Type         | Inverse Model   | Pivot/Table |
|--------------|--------------|-----------------|-------------|
| company      | belongsTo    | Company         | company_id  |
| vehicle      | belongsTo    | Vehicle         | vehicle_id  |
| technician   | belongsTo    | User            | technician_id |
| services     | belongsToMany| Service         | order_services |
| payments     | hasMany      | Payment         | -           |
| payment      | hasOne       | Payment         | -           |
| invoice      | hasOne       | Invoice         | -           |
| statusLogs   | hasMany      | OrderStatusLog  | -           |
| attachments  | hasMany      | Attachment      | -           |
| notifications| morphMany    | Notification    | (polymorphic) |

---

## 5. Payment

| Relation   | Type      | Inverse Model | Notes                          |
|------------|-----------|---------------|--------------------------------|
| order      | belongsTo | Order         | order_id                       |
| bankAccount| belongsTo | BankAccount   | bank_account_id                |
| company    | *(accessor)* | -          | `$payment->order->company`     |

**Note:** `company_id` was removed from `payments` table. Company is accessed via `order->company`.

---

## 6. Invoice

| Relation | Type      | Inverse Model | Notes                    |
|----------|-----------|---------------|--------------------------|
| order    | belongsTo | Order         | order_id                 |
| company  | belongsTo | Company       | company_id (redundant?)   |
| payments | hasMany   | Payment       | order_id (shared) – `hasMany(Payment::class, 'order_id', 'order_id')` |

**Note:** Invoice has both `order_id` and `company_id`. Company can be derived from `order->company`. Consider removing `company_id` for consistency with Payment.

---

## 7. User (Admin / Technician)

| Relation          | Type    | Inverse Model   | FK Column    |
|-------------------|---------|-----------------|--------------|
| assignedOrders    | hasMany | Order           | technician_id|
| technicianLocations | hasMany| TechnicianLocation | technician_id |
| notifications     | morphMany | Notification  | (polymorphic) |

---

## 8. Service

| Relation  | Type         | Inverse Model | Pivot/Table   |
|-----------|--------------|---------------|---------------|
| companies | belongsToMany| Company       | company_services |
| orders    | belongsToMany| Order         | order_services (pivot: qty, unit_price, total_price) |
| inventory | hasOne       | InventoryItem | service_id       |

---

## 9. Rating

| Relation    | Type      | Inverse Model |
|-------------|-----------|---------------|
| order       | belongsTo | Order         |
| company     | belongsTo | Company       |
| technician  | belongsTo | User          |
| vehicle     | belongsTo | Vehicle       |

---

## 10. Other Models

| Model              | Key Relations                                    |
|--------------------|--------------------------------------------------|
| **OrderStatusLog** | order (belongsTo), changedBy (User)              |
| **Attachment**    | order (belongsTo), uploader (User)               |
| **BankAccount**   | No relations (standalone)                         |
| **InventoryItem** | service (belongsTo)                              |
| **InventoryTransaction** | item (InventoryItem), order, creator (User) |
| **TechnicianLocation**  | technician (User)                        |
| **Notification**   | Polymorphic (notifiable: Company, User, Order)   |
| **Activity**       | No Eloquent relations (uses direct columns)     |

---

## Redundancy Check

| Table   | Redundant? | Suggestion                                      |
|---------|------------|-------------------------------------------------|
| payments| Fixed      | `company_id` removed; use `order->company`      |
| invoices| Possible   | Has `company_id`; could use `order->company`    |
| ratings | OK         | Links order, company, technician, vehicle       |

---

## Pivot Tables

| Table            | Left       | Right    | Purpose                      |
|------------------|------------|----------|------------------------------|
| company_services | companies  | services | Company-specific pricing     |
| order_services   | orders     | services | Order line items (qty, unit_price, total_price) |
