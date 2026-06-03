# 🏦 Bank System API

A full-featured RESTful banking API built with **Laravel 11** and **Sanctum token authentication**. Supports user auth, bank account management, deposits/withdrawals/transfers, loans with installments, monthly PDF statements, real-time notifications, and a full admin panel.

**Live demo:** https://your-netlify-app.netlify.app  
**API base URL:** https://s-bank-backend-production.up.railway.app

---

## 📋 Table of Contents

- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Getting Started](#getting-started)
- [Authentication](#authentication)
- [API Reference](#api-reference)
  - [Auth](#auth)
  - [Email Verification](#email-verification)
  - [Account](#account)
  - [Transactions](#transactions)
  - [Loans](#loans)
  - [Statement](#statement)
  - [Notifications](#notifications)
  - [Settings](#settings)
  - [Admin](#admin)
- [Middleware Reference](#middleware-reference)
- [Error Responses](#error-responses)
- [Testing](#testing)

---

## 🛠 Tech Stack

| Layer        | Technology                          |
|--------------|-------------------------------------|
| Backend      | Laravel 11                          |
| Auth         | Laravel Sanctum (token-based)       |
| Database     | MySQL (Railway) / SQLite (testing)  |
| PDF          | barryvdh/laravel-dompdf             |
| Cache        | File / Redis                        |
| Queue        | Database / Redis                    |
| Mail         | SMTP                                |
| Frontend     | React + Tailwind CSS (Netlify)      |

---

## 🏗 Architecture

```
app/
├── Http/
│   ├── Controllers/        # Thin controllers — HTTP layer only
│   ├── Middleware/         # Auth, role, account state guards
│   └── Requests/           # Form request validation
├── Services/               # Business logic (CreateAccount, Transfer, etc.)
├── Repositories/           # Data access layer
│   └── Interfaces/         # Repository contracts
├── Models/                 # Eloquent models with query scopes
├── Notifications/          # Laravel notification classes
├── Jobs/                   # Queued jobs (monthly statements)
├── Mail/                   # Mailable classes
└── Console/Commands/       # Scheduled commands
```

**Request flow:**
```
Request → Middleware → Controller → Service → Repository → Model → Database
```

---

## 🚀 Getting Started

### Requirements

- PHP 8.2+
- Composer
- MySQL
- Node.js (for frontend)

### Installation

```bash
# Clone the repository
git clone https://github.com/kaisshaheen/S-Bank---Backend
cd bank-system

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bank_system
DB_USERNAME=root
DB_PASSWORD=

# Run migrations and seeders
php artisan migrate
php artisan db:seed --class=AdminSeeder

# Start the server
php artisan serve

# Start the queue worker (required for emails and notifications)
php artisan queue:work

# Start the scheduler (required for monthly statements)
php artisan schedule:work
```

---

## 🔐 Authentication

This API uses **Laravel Sanctum token authentication**.

Every request must include:
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer <token>
```

Tokens are returned on login and register. Store the token and attach it to every subsequent request.

---

## 📡 API Reference

Base URL: `https://s-bank-backend-production.up.railway.app/api`

---

### Auth

#### Register
```
POST /register
```

**Body:**
```json
{
  "name": "Ahmed Hassan",
  "email": "ahmed@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response `201`:**
```json
{
  "token": "1|abc123...",
  "user": {
    "name": "Ahmed Hassan",
    "email": "ahmed@example.com",
    "role": "user"
  }
}
```

---

#### Login
```
POST /login
```

**Body:**
```json
{
  "email": "ahmed@example.com",
  "password": "password123"
}
```

**Response `200`:**
```json
{
  "token": "1|abc123...",
  "user": {
    "name": "Ahmed Hassan",
    "email": "ahmed@example.com",
    "role": "user"
  }
}
```

---

#### Logout
```
POST /logout
```
🔒 Requires authentication.

Deletes the current access token.

**Response `200`:**
```json
{
  "message": "Logged out successfully"
}
```

---

#### Get Authenticated User
```
GET /user
```
🔒 Requires authentication.

**Response `200`:**
```json
{
  "user": {
    "name": "Ahmed Hassan",
    "email": "ahmed@example.com",
    "role": "user",
    "verified": "2026-01-01T00:00:00.000000Z"
  }
}
```

---

### Email Verification

#### Verify Email
```
GET /email/verify/{id}/{hash}
```
Signed URL sent to the user's email after registration. Handled automatically.

---

#### Resend Verification Email
```
POST /email/verification-notification
```
🔒 Requires authentication + unverified email.  
Rate limited to **6 requests per minute**.

**Response `200`:**
```json
{
  "message": "Verification email sent"
}
```

---

### Password Reset

#### Send Reset Link
```
POST /forgot-password
```

**Body:**
```json
{
  "email": "ahmed@example.com"
}
```

**Response `200`:**
```json
{
  "message": "Reset link sent to your email"
}
```

---

#### Reset Password
```
POST /reset-password
```

**Body:**
```json
{
  "token": "reset-token-from-email",
  "email": "ahmed@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response `200`:**
```json
{
  "message": "Password reset successfully"
}
```

---

### Account

> All account routes require: `auth:sanctum` + `email.verified.api` + `check.banned`

#### Create Account
```
POST /account/create
```
Middleware: `no.account` — user must not already have an account.

**Body:**
```json
{
  "type": "saving",
  "national_number": "12345678901",
  "password": "account_password",
  "password_confirmation": "account_password"
}
```

| Field | Rules |
|---|---|
| `type` | `required` · `in:saving,current` |
| `national_number` | `required` · `string` · exactly 11 characters · unique |
| `password` | `required` · `string` · min 8 characters · confirmed |

**Response `201`:**
```json
{
  "message": "Account created successfully",
  "account": {
    "account_number": "ACC-847291038",
    "type": "saving",
    "balance": 0,
    "status": "active"
  }
}
```

---

#### Login to Account
```
POST /account/login
```
Middleware: `has.account`

The bank account has a separate password from the user login password.

**Body:**
```json
{
  "password": "account_password"
}
```

**Response `200`:**
```json
{
  "message": "Login successful",
  "account": {
    "account_number": "ACC-847291038",
    "balance": 4250.00
  }
}
```

---

#### Get My Account
```
GET /account
```
Middleware: `has.account`

Response is cached per user and invalidated on every transaction.

**Response `200`:**
```json
{
  "account": {
    "account_owner": "Ahmed Hassan",
    "account_number": "ACC-847291038",
    "balance": 4250.00
  }
}
```

---

### Transactions

> All transaction routes require: `has.account` + `account.active`

#### Deposit
```
POST /transaction/deposit
```

**Body:**
```json
{
  "amount": 500.00
}
```

**Response `200`:**
```json
{
  "message": "Deposit successful",
  "transaction": { ... }
}
```

---

#### Withdraw
```
POST /transaction/withdraw
```

**Body:**
```json
{
  "amount": 200.00
}
```

**Response `200`:**
```json
{
  "message": "Withdrawal successful",
  "transaction": { ... }
}
```

Returns `422` if balance is insufficient.

---

#### Transfer
```
POST /transaction/transfer
```

**Body:**
```json
{
  "to_account": "ACC-229183740",
  "amount": 300.00
}
```

**Response `200`:**
```json
{
  "message": "Transfer successful",
  "transaction": { ... }
}
```

> Uses **pessimistic locking** (`lockForUpdate`) to prevent race conditions.  
> The recipient receives a database notification automatically.

Returns `422` if balance is insufficient or recipient account not found.

---

#### Transaction History
```
GET /transaction/history?page=1
```

Paginated (10 per page). Results cached per page and invalidated on new transactions.

**Response `200`:**
```json
{
  "transactions": {
    "data": [ ... ],
    "current_page": 1,
    "last_page": 5,
    "total": 48,
    "from": 1,
    "to": 10
  }
}
```

---

### Loans

#### Get My Loan
```
GET /loan
```
Middleware: `has.account`

**Response `200`:**
```json
{
  "id": 1,
  "amount": 10000,
  "interest_rate": 5.5,
  "duration_months": 12,
  "total_payable": 10550,
  "status": "approved",
  "purpose": "personal",
  "installments": [
    {
      "id": 1,
      "month_number": 1,
      "amount": 879.17,
      "due_date": "2026-02-01",
      "status": "paid",
      "paid_at": "2026-02-01T10:00:00.000000Z"
    }
  ]
}
```

---

#### Request a Loan
```
POST /loan/create
```
Middleware: `has.account` + `no.active.loan`

A user can only have one pending or approved loan at a time.

**Body:**
```json
{
  "amount": 10000,
  "duration_months": 12,
  "purpose": "personal"
}
```

| Field | Rules |
|---|---|
| `amount` | `required` · `numeric` · min 100 · max 50000 |
| `duration_months` | `required` · `integer` · `in:3,6,12,24,36` |
| `purpose` | `required` · `in:personal,business,education,medical,other` |

**Response `201`:**
```json
{
  "message": "Loan request submitted successfully. Awaiting admin approval."
}
```

---

#### Pay Installment
```
POST /loan/installment/{installment}/pay
```
Middleware: `has.account` + `account.active` + `owns.installment`

**Rules enforced:**
- Installments must be paid in order — cannot skip
- Account must have sufficient balance
- Installment must not already be paid

**Response `200`:**
```json
{
  "message": "Installment paid successfully"
}
```

Returns `422` for insufficient balance, already paid, or out-of-order payment.

---

### Statement

#### Get Statement
```
GET /statement?from=2026-01-01&to=2026-04-30&type=deposit
```
Middleware: `has.account`

| Parameter | Type   | Required | Description                          |
|-----------|--------|----------|--------------------------------------|
| `from`    | date   | No       | Filter from date `Y-m-d`             |
| `to`      | date   | No       | Filter to date `Y-m-d`               |
| `type`    | string | No       | `deposit`, `withdraw`, or `transfer` |

**Response `200`:**
```json
{
  "statement": {
    "data": [ ... ],
    "summary": {
      "total_in": 5000.00,
      "total_out": 1200.00,
      "net": 3800.00
    }
  }
}
```

---

#### Download Monthly PDF Statement
```
POST /statement/monthly-pdf
```
Middleware: `has.account`

**Body:**
```json
{
  "month": 4,
  "year": 2026
}
```

**Response:** Binary PDF (`application/pdf`)

> Use `responseType: 'blob'` in Axios to handle the binary response correctly.

---

### Notifications

#### Get Notifications
```
GET /notifications
```

**Response `200`:**
```json
{
  "notifications": [
    {
      "id": "uuid",
      "data": {
        "message": "You received $500.00",
        "from_account": "ACC-229183740"
      },
      "read_at": null,
      "created_at": "2026-04-14T10:00:00.000000Z"
    }
  ],
  "unread_count": 3
}
```

---

#### Mark Notification as Read
```
POST /notifications/{id}/read
```

**Response `200`:**
```json
{
  "message": "Marked as read"
}
```

---

#### Mark All as Read
```
POST /notifications/read
```

**Response `200`:**
```json
{
  "message": "All marked as read"
}
```

---

### Settings

#### Update Name
```
PATCH /settings/name
```

**Body:**
```json
{
  "name": "Ahmed Mohamed"
}
```

**Response `200`:**
```json
{
  "message": "Name updated successfully"
}
```

---

#### Update Password
```
PATCH /settings/password
```

**Body:**
```json
{
  "current_password": "oldpassword123",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response `200`:**
```json
{
  "message": "Password updated successfully"
}
```

Returns `422` if current password is incorrect.

---

### Admin

> All admin routes require: `auth:sanctum` + `email.verified.api` + `check.banned` + `role.admin`

Base prefix: `/admin`

---

#### Dashboard Metrics
```
GET /admin/dashboard
```

**Response `200`:**
```json
{
  "total_users": 1284,
  "total_accounts": 1190,
  "total_money": 4200000.00,
  "total_active_loans": 42,
  "pending_loans": 3,
  "overdue_installments": 7,
  "deposits_today": 28400.00,
  "withdrawals_today": 9200.00,
  "recent_transactions": [ ... ],
  "pending_loan_list": [ ... ]
}
```

---

#### List Accounts
```
GET /admin/accounts?search=Ahmed&status=active&type=saving&page=1
```

| Parameter | Description |
|---|---|
| `search` | Filter by owner name or account number |
| `status` | `active` or `suspended` |
| `type` | `saving` or `current` |

---

#### Get Account
```
GET /admin/accounts/{account}
```

---

#### Toggle Account Status
```
POST /admin/accounts/{account}/toggle-status
```
Toggles between `active` and `suspended`.

**Response `200`:**
```json
{
  "message": "Account status updated",
  "account": { ... }
}
```

---

#### List Loans
```
GET /admin/loans?search=Ahmed&status=pending&page=1
```

| Parameter | Description |
|---|---|
| `search` | Filter by account owner name |
| `status` | `pending`, `approved`, or `rejected` |

---

#### Approve or Reject Loan
```
POST /admin/loans/{loan}/{action}
```

`{action}` must be `approve` or `reject`.

> Approving a loan automatically generates all installments based on `total_payable / duration_months`.

**Response `200`:**
```json
{
  "message": "Loan approved successfully"
}
```

Returns `422` if the loan is not in `pending` status or action is invalid.

---

#### List Transactions
```
GET /admin/transactions?search=Ahmed&type=deposit&from=2026-01-01&to=2026-04-30&page=1
```

| Parameter | Description |
|---|---|
| `search` | Filter by account owner name |
| `type` | `deposit`, `withdraw`, or `transfer` |
| `from` | Start date `Y-m-d` |
| `to` | End date `Y-m-d` |

---

#### List Users
```
GET /admin/users?search=Ahmed&role=user&status=active&page=1
```

| Parameter | Description |
|---|---|
| `search` | Filter by name or email |
| `role` | `user` or `admin` |
| `status` | `active` or `banned` |

---

#### Get User
```
GET /admin/users/{user}
```

---

#### Ban / Unban User
```
POST /admin/users/{user}/ban
```

Toggles between `active` and `banned`. Admins cannot be banned.

**Response `200`:**
```json
{
  "message": "User status updated",
  "user": { ... }
}
```

---

## 🛡 Middleware Reference

| Alias | Class | Description |
|---|---|---|
| `auth:sanctum` | Laravel built-in | Requires valid Bearer token |
| `email.verified.api` | `EmailIsVerified` | Requires verified email, returns 403 otherwise |
| `check.banned` | `CheckUserBanned` | Blocks banned users with 403 |
| `role.admin` | `EnsureUserIsAdmin` | Requires `role = admin`, returns 403 otherwise |
| `has.account` | `EnsureUserHasAccount` | Requires user to have a bank account |
| `no.account` | `EnsureUserHasNoAccount` | Requires user to NOT have a bank account |
| `account.active` | `EnsureAccountIsActive` | Requires account `status = active` |
| `no.active.loan` | `EnsureNoActiveLoan` | Blocks if user has a pending or approved loan |
| `owns.installment` | `EnsureInstallmentOwnership` | Verifies installment belongs to user's account |
| `no.verified.email` | `VerifiedEmail` | Requires email to NOT be verified (resend route only) |

---

## ⚠️ Error Responses

| Status | Meaning |
|---|---|
| `401` | Unauthenticated — missing or invalid token |
| `403` | Forbidden — banned, wrong role, middleware blocked |
| `404` | Resource not found |
| `422` | Validation error or business rule violation |
| `500` | Server error — check Laravel log |

**Validation error format:**
```json
{
  "message": "The amount field is required.",
  "errors": {
    "amount": ["The amount field is required."]
  }
}
```

**General error format:**
```json
{
  "message": "Insufficient balance"
}
```

---

## 🧪 Testing

The project includes **feature tests** covering all critical paths:

```bash
# Run all tests
php artisan test

# Run a specific test file
php artisan test tests/Feature/TransactionTest.php

# Run with detailed output
php artisan test --verbose
```

**Test coverage:**

| File | Tests |
|---|---|
| `AuthTest` | Register, login, logout, banned user, wrong password |
| `AccountTest` | Create account, duplicate prevention, login, view, unique account numbers |
| `TransactionTest` | Deposit, withdraw, transfer, insufficient balance, suspended account, notifications |
| `LoanTest` | Request loan, duplicate prevention, admin approve/reject, pay installment, skip prevention |

Tests use **SQLite in-memory** database with `RefreshDatabase` — no setup required.

---

## 📅 Scheduled Jobs

| Command | Schedule | Description |
|---|---|---|
| `bank:send-statements` | 1st of every month at 01:00 | Generates and emails PDF statements to all account holders |

```bash
# Run scheduler locally
php artisan schedule:work

# Trigger manually
php artisan bank:send-statements
```

---

## 👤 Author

Built as a portfolio project demonstrating full-stack banking system architecture with Laravel and React.

- **GitHub:** https://github.com/kaisshaheen