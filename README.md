<div align="center">

<img src="https://capsule-render.vercel.app/api?type=waving&height=240&color=0:0B1020,35:1E3A8A,70:4F46E5,100:22D3EE&text=TASKFLOW%20AI&fontColor=FFFFFF&fontSize=56&fontAlignY=38&desc=TASK%20MANAGEMENT%20API%20%7C%20LARAVEL%20%7C%20SANCTUM&descSize=15&descAlignY=59&animation=fadeIn" width="100%" alt="TaskFlow AI — Task Management API banner" />

### A secure, test-driven API for managing tasks.

A Laravel-powered task management backend with token authentication, per-user task isolation, filtering, search, sorting, and pagination — built with maintainability and API clarity in mind.

<p>
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13" />
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3+" />
  <img src="https://img.shields.io/badge/Auth-Laravel%20Sanctum-4F46E5?style=for-the-badge" alt="Laravel Sanctum authentication" />
  <img src="https://img.shields.io/badge/Tests-Pest-16A34A?style=for-the-badge" alt="Pest tests" />
  <img src="https://img.shields.io/badge/Environment-Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker" />
</p>

<p>
  <a href="https://github.com/OrpanAp/taskflow-api"><img src="https://img.shields.io/badge/Repository-View%20source-111827?style=flat-square&logo=github&logoColor=white" alt="View source code" /></a>
  <a href="https://laravel.com/docs"><img src="https://img.shields.io/badge/Docs-Laravel-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel documentation" /></a>
</p>

</div>

---

## Overview

**TaskFlow AI** is a task-management API built with Laravel. It gives authenticated users a consistent way to create and manage their own tasks, while keeping each user's records isolated from everyone else's.

The project emphasizes practical backend engineering: clear route organization, request validation, token-based authentication, query controls for growing task lists, and automated feature tests.

## Features

- **Token authentication** — register and log in with Laravel Sanctum; revoke the current token when logging out.
- **Protected task management** — create, list, update, and delete tasks through authenticated endpoints.
- **User-level data isolation** — task queries are scoped to the authenticated user.
- **Search** — search task titles and descriptions with case-insensitive partial matching.
- **Status filtering** — narrow task lists by task status.
- **Sorting** — choose supported ordering for task results.
- **Pagination** — return task collections in manageable pages with pagination metadata.
- **Validation and rate limiting** — validate incoming data and throttle registration/login attempts.
- **Automated tests** — feature tests cover authentication, task ownership, validation, search, filtering, sorting, and pagination.
- **Docker-based development** — run the application in a consistent container environment.

## Technology stack

| Area | Technologies |
|---|---|
| Backend | PHP, Laravel 13 |
| Authentication | Laravel Sanctum personal access tokens |
| Data access | Eloquent ORM |
| Testing | Pest, Laravel feature testing |
| Runtime | Docker / Docker Compose |
| API format | JSON over HTTP |

## Getting started

### Prerequisites

- Git
- Docker Desktop or Docker Engine with the Docker Compose plugin

### 1. Clone the repository

```bash
git clone https://github.com/OrpanAp/taskflow-api.git
cd taskflow-api
```

### 2. Configure environment

Create your local environment file from the example:

```bash
cp .env.example .env
```

Review the environment variables and make sure the database settings match the services configured for your Docker Compose environment.

### 3. Build and start the application

```bash
docker compose up -d --build app
```

If you change application source files, rebuild the app image so the container uses the latest code:

```bash
docker compose up -d --build app
```

### 4. Prepare Laravel

Run the following commands inside the application container:

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

If your environment already has an application key, do not replace it unnecessarily. If migrations need a different deployment-specific flag, use the appropriate setting for that environment.

### 5. Check the API

With the application running locally, open:

```text
http://127.0.0.1:8000/api/health
```

Expected response:

```json
{
  "status": "ok",
  "application": "TaskFlow AI"
}
```

## API reference

Base URL for local development: `http://127.0.0.1:8000/api`

### Public endpoints

| Method | Endpoint | Purpose |
|---|---|---|
| `GET` | `/health` | Check API health |
| `POST` | `/register` | Create an account and receive a token |
| `POST` | `/login` | Authenticate and receive a token |

Registration and login are rate-limited.

### Authenticated endpoints

Send the token returned by registration or login in the request header:

```http
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

| Method | Endpoint | Purpose |
|---|---|---|
| `POST` | `/logout` | Revoke the current access token |
| `GET` | `/user` | Get the authenticated user's information |
| `GET` | `/tasks` | List the authenticated user's tasks |
| `POST` | `/tasks` | Create a task |
| `PUT` | `/tasks/{task}` | Update a task |
| `DELETE` | `/tasks/{task}` | Delete a task |

All task endpoints require authentication. Users can access only tasks belonging to their own account.

### Example: register

```http
POST /api/register
Content-Type: application/json
Accept: application/json
```

```json
{
  "name": "Alex Developer",
  "email": "alex@example.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!"
}
```

A successful registration returns a user representation and an access token. Keep the token private.

### Example: create a task

```http
POST /api/tasks
Authorization: Bearer YOUR_ACCESS_TOKEN
Content-Type: application/json
Accept: application/json
```

```json
{
  "title": "Finish API documentation",
  "description": "Document endpoints and test the examples.",
  "status": "pending"
}
```

New tasks default to the `pending` status when one is not supplied.

### List, search, filter, sort, and paginate tasks

The task-list endpoint supports query parameters:

| Parameter | Example | Purpose |
|---|---|---|
| `search` | `?search=laravel` | Partial, case-insensitive search across title and description |
| `status` | `?status=in_progress` | Filter by task status |
| `sort` | `?sort=oldest` | Select a supported sort order |
| `page` | `?page=2` | Request a page of results |

Parameters can be combined, for example:

```http
GET /api/tasks?search=laravel&status=completed&sort=oldest&page=1
```

The list response includes pagination metadata such as `current_page`, `per_page`, `total`, and `last_page`. Invalid parameter values return validation errors rather than silently changing the requested query.

## Testing

Run the Pest feature suite inside the app container:

```bash
docker compose exec app sh -lc 'touch .env && APP_ENV=testing ./vendor/bin/pest'
```

The test configuration uses an isolated in-memory SQLite database, so feature tests do not need to write to the development PostgreSQL database.

Coverage includes:

- Registration and login validation
- Authentication requirements for protected endpoints
- Task creation and required fields
- Task ownership and private data isolation
- Search, including partial and case-insensitive matches
- Status filtering and sort validation
- Pagination and combined query parameters

## Project structure

```text
app/
  Http/
    Controllers/
      Api/          # Authentication and task endpoints
    Resources/      # Consistent API resource responses
  Models/           # User and Task models
routes/
  api.php           # API route definitions
  web.php           # Browser-facing routes
tests/
  Feature/          # API and application behavior tests
```

## Security notes

- Keep access tokens and environment secrets out of source control.
- Use HTTPS in production.
- Keep authentication and task endpoints protected by the appropriate middleware.
- Validate request data and return safe user representations rather than exposing password fields.
- Review rate limits and operational settings before deploying to production.

## Roadmap

Potential next steps as the project evolves:

- Expand API documentation with a machine-readable OpenAPI specification.
- Add more edge-case and authorization tests.
- Improve deployment and CI automation.
- Continue refining the browser dashboard alongside the API.

## About the developer

Built by **Alex Purification**, a Computer Science and Engineering graduate focused on full-stack development, backend APIs, and practical software engineering.

<p align="center">
  <a href="https://github.com/OrpanAp">GitHub</a> ·
  <a href="https://www.linkedin.com/in/alex-purification/">LinkedIn</a> ·
  <a href="https://alex-purification.page.gd/">Portfolio</a>
</p>

<div align="center">

<img src="https://capsule-render.vercel.app/api?type=waving&section=footer&height=115&color=0:0B1020,40:1E3A8A,100:22D3EE" width="100%" alt="" />

*Built with care. Designed for clarity. Improved through testing.*

</div>
