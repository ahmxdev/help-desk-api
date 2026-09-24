# Overview

This project is a Laravel REST API for a help desk system. It supports customer, agent, and admin roles and provides the core flows for authentication, ticket management, and in-ticket messaging.

The application exposes JSON endpoints for user registration and login, email verification, password reset, ticket creation and assignment, and role-based authorization checks. It is implemented as a backend API with Laravel, Sanctum, and database-backed session and queue configuration.

# Features

- User registration and login with Laravel Sanctum tokens
- Authenticated profile retrieval and logout
- Email verification and resend-verification flows
- Forgot password, reset password, and change password endpoints
- Role-based access for customer, agent, and admin users
- Ticket creation, listing, detail retrieval, status updates, and priority updates
- Agent assignment to tickets
- Ticket messaging through a message sending endpoint
- Admin user listing and role reassignment
- Permission and role checks enforced through middleware and a ticket policy

# Tech Stack

- PHP 8.3
- Laravel 13
- Laravel Sanctum
- MySQL
- Vite
- Pest PHP
- REST API architecture

# API Endpoints

## Authentication

| Method | Endpoint        | Description                                      |
| ------ | --------------- | ------------------------------------------------ |
| `POST` | `/api/register` | Register a new user and assign the customer role |
| `POST` | `/api/login`    | Authenticate a user and return a Sanctum token   |
| `POST` | `/api/logout`   | Revoke the current access token                  |
| `GET`  | `/api/me`       | Get the authenticated user's profile             |

## Email Verification and Password Management

| Method | Endpoint                               | Description                                             |
| ------ | -------------------------------------- | ------------------------------------------------------- |
| `GET`  | `/api/email/verify/{id}/{hash}`        | Verify a user's email address using a signed URL        |
| `POST` | `/api/email/verification-notification` | Send a new verification email to the authenticated user |
| `POST` | `/api/forgot-password`                 | Request a password reset link                           |
| `POST` | `/api/reset-password`                  | Reset a user's password using a valid token             |
| `POST` | `/api/change-password`                 | Change the authenticated user's password                |

## Tickets

| Method  | Endpoint                         | Description                                                       |
| ------- | -------------------------------- | ----------------------------------------------------------------- |
| `GET`   | `/api/tickets`                   | List tickets for the authenticated user or all tickets for admins |
| `GET`   | `/api/tickets/{ticket}`          | Show a specific ticket                                            |
| `POST`  | `/api/tickets`                   | Create a new ticket                                               |
| `PATCH` | `/api/tickets/{ticket}/status`   | Update a ticket status                                            |
| `PATCH` | `/api/tickets/{ticket}/priority` | Update a ticket priority                                          |
| `PATCH` | `/api/tickets/{ticket}/agent`    | Assign an agent to a ticket                                       |

## Messages

| Method | Endpoint        | Description                |
| ------ | --------------- | -------------------------- |
| `POST` | `/api/messages` | Send a message on a ticket |

## Users

| Method  | Endpoint                 | Description                                  |
| ------- | ------------------------ | -------------------------------------------- |
| `GET`   | `/api/users`             | List all users with their roles (admin only) |
| `PATCH` | `/api/users/{user}/role` | Set a user's role (admin only)               |

# Installation & Setup

## 1. Clone the repository

```bash
git clone <repository-url>
cd help-desk-api
```

## 2. Install dependencies

```bash
composer install
```

## 3. Configure the environment

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure your database credentials in `.env` and create the database.

## 4. Run migrations

```bash
php artisan migrate
```

## 5. Start the development server

```bash
php artisan serve
```

The API will be available at:

```text
http://127.0.0.1:8000
```

# Tests

The project uses Pest for automated testing.

The suite covers:

- Authentication flows
- Email verification and password reset flows
- Ticket creation and ticket permissions
- Message sending rules
- User role assignment and admin restrictions
- Model-level permission and role behavior

Run the full test suite with:

```bash
php artisan test
```

The project also exposes:

```bash
composer test
```

# Architecture Decisions

- **Laravel REST API with Sanctum:** Authentication uses Laravel Sanctum personal access tokens. Protected routes attach the `auth:sanctum` middleware and API responses are returned as JSON.

- **Role and permission model:** Users are linked to roles, and roles are linked to permissions. The application uses `hasRole`, `hasPermission`, and custom middleware aliases named `role` and `permission` to enforce access control.

- **Ticket policy for update actions:** `TicketPolicy` restricts ticket status and priority updates so that only admins can update any ticket and assigned agents can update their own tickets.

- **JSON resources for response shaping:** The API uses resource classes such as `TicketResource`, `CustomerTicketResource`, `UserResource`, and `MessageResource` to format responses differently for customer and agent/admin views.

- **Password reset links target a frontend URL:** `AppServiceProvider` configures `ResetPassword::createUrlUsing()` to build reset URLs from `FRONTEND_URL`, which is set in the environment file because no frontend application is included in this repository.

- **API exception handling:** `bootstrap/app.php` configures exceptions to render JSON for API requests, so API responses remain consistent across authentication and authorization failures.
