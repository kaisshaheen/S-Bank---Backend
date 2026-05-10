# Banking API Documentation

A RESTful Banking API built with Laravel and Sanctum session-based authentication.

---

# Base URL

```bash
http://your-domain.com/api
```

---

# Authentication

This API uses Laravel Sanctum with **session cookie authentication**.

After login, Laravel automatically authenticates the user using session cookies.

Protected routes require:

* Valid session cookie
* CSRF protection (for SPA/web clients)

---

# CSRF Authentication Flow

Before making authenticated requests, first call:

```http
GET /sanctum/csrf-cookie
```

Laravel will return the CSRF cookie required for session authentication.

Then login using:

```http
POST /login
```

The session cookie will automatically authenticate future requests.

---

# Auth Endpoints

## Register User

### Endpoint

```http
POST /register
```

### Request Body

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

---

## Login

### Endpoint

```http
POST /login
```

### Request Body

```json
{
  "email": "john@example.com",
  "password": "password"
}
```

---

## Logout

### Endpoint

```http
POST /logout
```

---

# Email Verification

## Verify Email

### Endpoint

```http
GET /email/verify/{id}/{hash}
```

---

## Resend Verification Email

### Endpoint

```http
POST /email/verification-notification
```

### Middleware

* auth:sanctum
* no.verified.email
* throttle:6,1

---

# User Information

## Get Authenticated User

### Endpoint

```http
GET /user
```

---

# Password Reset

## Send Reset Link

### Endpoint

```http
POST /forgot-password
```

---

## Reset Password

### Endpoint

```http
POST /reset-password
```

---

# Account Endpoints

These routes require:

* auth:sanctum
* email.verified.api
* check.banned

---

## Create Account

```http
POST /account/create
```

---

## Login To Account

```http
POST /account/login
```

---

## Get My Account

```http
GET /account
```

---

# Transaction Endpoints

These routes require:

* has.account
* account.active

---

## Deposit Money

```http
POST /transcation/deposit
```

---

## Withdraw Money

```http
POST /transcation/withdraw
```

---

## Transfer Money

```http
POST /transcation/transfer
```

---

## Transaction History

```http
GET /transcation/history
```

---

# Loan Endpoints

## Get User Loan

```http
GET /loan
```

---

## Create Loan

```http
POST /loan/create
```

---

## Pay Loan Installment

```http
POST /loan/installment/{installment}/pay
```

---

# Statement Endpoints

## Get Statements

```http
GET /statement
```

---

## Generate Monthly PDF Statement

```http
POST /statement/monthly-pdf
```

---

# Notification Endpoints

## Get Notifications

```http
GET /notifications
```

---

## Mark All Notifications As Read

```http
POST /notifications/read
```

---

## Mark Single Notification As Read

```http
POST /notifications/{id}/read
```

---

# Admin Endpoints

All admin routes are prefixed with:

```http
/admin
```

Required Middleware:

* auth:sanctum
* email.verified.api
* check.banned
* role.admin

---

## Dashboard

```http
GET /admin/dashboard
```

---

## Accounts

### Get All Accounts

```http
GET /admin/accounts
```

### Get Single Account

```http
GET /admin/accounts/{account}
```

### Toggle Account Status

```http
POST /admin/accounts/{account}/toggle-status
```

---

## Loans

### Get All Loans

```http
GET /admin/loans
```

### Approve or Reject Loan

```http
POST /admin/loans/{loan}/{action}
```

Example:

```http
POST /admin/loans/1/approve
```

or

```http
POST /admin/loans/1/reject
```

---

## Transactions

```http
GET /admin/transactions
```

---

## Users

### Get All Users

```http
GET /admin/users
```

### Get Single User

```http
GET /admin/users/{user}
```

### Ban User

```http
POST /admin/users/{user}/ban
```

---

# Middleware Overview

| Middleware         | Description                        |
| ------------------ | ---------------------------------- |
| auth:sanctum       | Authenticated users only           |
| no.verified.email  | User email not verified            |
| email.verified.api | Email must be verified             |
| check.banned       | Prevent banned users               |
| no.account         | User must not already have account |
| has.account        | User must have account             |
| account.active     | Account must be active             |
| no.active.loan     | Prevent multiple active loans      |
| owns.installment   | User owns installment              |
| role.admin         | Admin only access                  |

---

# Status Codes

| Code | Meaning          |
| ---- | ---------------- |
| 200  | Success          |
| 201  | Created          |
| 401  | Unauthorized     |
| 403  | Forbidden        |
| 404  | Not Found        |
| 422  | Validation Error |
| 500  | Server Error     |

---

# Technologies Used

* Laravel
* Laravel Sanctum
* MySQL
* REST API