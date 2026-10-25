<div align="center">

<img src="https://capsule-render.vercel.app/api?type=waving&height=240&color=0:0B1020,35:1E3A8A,70:4F46E5,100:22D3EE&text=TASKFLOW%20AI&fontColor=FFFFFF&fontSize=56&fontAlignY=38&desc=AI-ASSISTED%20TASK%20MANAGEMENT%20%7C%20LARAVEL%20%7C%20OLLAMA&descSize=15&descAlignY=59&animation=fadeIn" width="100%" alt="TaskFlow AI — AI-assisted task management" />

### Plan clearly. Review AI suggestions. Stay in control.

**TaskFlow AI** is a Laravel application that combines a browser-based task dashboard and a JSON API with locally hosted AI task proposals. Generate a draft from a prompt, review it, and decide whether to save it—AI suggestions are not persisted until you approve them.

<p>
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13" />
  <img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4" />
  <img src="https://img.shields.io/badge/PostgreSQL-17-4169E1?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL 17" />
  <img src="https://img.shields.io/badge/AI-Ollama-111827?style=for-the-badge" alt="Ollama local AI" />
  <img src="https://img.shields.io/badge/Tests-Pest-16A34A?style=for-the-badge" alt="Pest tests" />
  <img src="https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker Compose" />
</p>

<p>
  <a href="https://github.com/OrpanAp/taskflow-api"><img src="https://img.shields.io/badge/Repository-Source%20Code-111827?style=flat-square&logo=github&logoColor=white" alt="Source code" /></a>
  <a href="https://laravel.com/docs"><img src="https://img.shields.io/badge/Docs-Laravel-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel documentation" /></a>
  <a href="https://ollama.com/"><img src="https://img.shields.io/badge/Local%20AI-Ollama-111827?style=flat-square" alt="Ollama" /></a>
</p>

</div>

---

## Contents

- [Overview](#overview)
- [Features](#features)
- [Architecture](#architecture)
- [Technology stack](#technology-stack)
- [Run locally with Docker](#run-locally-with-docker)
- [Configure local AI with Ollama](#configure-local-ai-with-ollama)
- [Use the dashboard](#use-the-dashboard)
- [API reference](#api-reference)
- [Run the tests](#run-the-tests)
- [Security and reliability](#security-and-reliability)
- [Project structure](#project-structure)
- [Roadmap](#roadmap)
- [About the developer](#about-the-developer)

## Overview

TaskFlow AI demonstrates practical full-stack and backend engineering through a task-management workflow with clear ownership boundaries, validation, automated tests, and a local AI integration.

The application has two interfaces:

- **Browser dashboard:** register or sign in, view task statistics, manage your tasks, and generate AI task proposals.
- **JSON API:** authenticate with Laravel Sanctum tokens and manage tasks using standard HTTP endpoints.

The AI integration uses an Ollama model running locally on the host machine by default. The model suggests a title and description; the user remains in control of saving the task.

## Features

### Task management

- Create, list, update, and delete your own tasks through the API.
- Create and list tasks through the authenticated browser dashboard.
- Search titles and descriptions using case-insensitive partial matching.
- Filter by task status, sort results, and paginate large task lists.
- View task statistics in the dashboard.
- Validate requests and reject invalid query options.

### AI task proposals

- Describe a task in natural language and request a draft from Ollama.
- Receive a structured title and description for review.
- Approve a proposal to save it as a task, or discard it without saving.
- Validate AI output and provide a fallback description when the model's description is missing, null, or empty.
- Handle unavailable providers and unusable AI responses with controlled errors.
- Rate-limit the AI proposal endpoint.

### Authentication and access control

- Register, log in, and log out in the browser.
- Register and log in through the API using Laravel Sanctum personal access tokens.
- Protect private routes with authentication middleware.
- Scope task queries to the authenticated user and enforce task ownership.
- Apply request validation and rate limiting to sensitive entry points.

## Architecture

    flowchart TD
        U[User] --> W[Laravel browser dashboard]
        U --> C[Client / API consumer]
        W --> L[Laravel application]
        C --> A[Laravel JSON API]
        L --> DB[(PostgreSQL 17)]
        A --> DB
        L --> AI[AI task proposal service]
        AI --> O[Ollama on host]
        O --> AI
        AI --> L
        L --> W

- **Laravel** handles routing, authentication, validation, task logic, and HTTP responses.
- **PostgreSQL** persists application data in the Docker Compose environment.
- **Laravel Sanctum** issues personal access tokens for API authentication.
- **Ollama** runs the configured language model on the host; the application sends proposal requests to its HTTP API.
- **Pest** tests application behavior, including authentication, ownership, query parameters, and AI failure/fallback cases.

## Technology stack

| Area | Technology |
|---|---|
| Backend framework | Laravel 13 |
| Runtime | PHP 8.4 Docker image |
| Database | PostgreSQL 17 |
| API authentication | Laravel Sanctum |
| ORM | Eloquent |
| Browser UI | Blade, JavaScript, Tailwind CSS / Vite |
| Local AI | Ollama; default model llama3.2:latest |
| Automated tests | Pest |
| Local environment | Docker Compose |

## Run locally with Docker

### Prerequisites

- Git
- Docker Desktop on Windows/macOS, or Docker Engine with the Compose plugin on Linux
- Ollama is optional for the main application, but required for live AI proposal generation

### 1. Clone the repository

    git clone https://github.com/OrpanAp/taskflow-api.git
    cd taskflow-api

### 2. Create your local environment file

    cp .env.example .env

On PowerShell, use:

    Copy-Item .env.example .env

The Compose configuration supplies the PostgreSQL connection settings and Ollama defaults to the app container. Keep your local .env file private; never commit real credentials or application secrets.

### 3. Build and start the services

    docker compose up -d --build app
    docker compose ps

Compose starts the Laravel app and PostgreSQL service. The app is published on 127.0.0.1:8000, and PostgreSQL is published on 127.0.0.1:5432 for local development.

### 4. Generate and persist the application key

The Compose file passes values from the host .env file into the container; it does not bind-mount that file for editing. Generate a key and copy it into the host .env file so it survives container rebuilds.

Run:

    docker compose exec app php artisan key:generate --show

Copy the displayed base64:... value into the host .env file as the value of APP_KEY, for example:

    APP_KEY=base64:PASTE_THE_GENERATED_VALUE_HERE

Do not use the example placeholder literally. Keep the same key for an existing local environment; changing it can invalidate encrypted data and sessions.

### 5. Run database migrations

    docker compose exec app php artisan migrate

### 6. Open the application

- Dashboard: [http://127.0.0.1:8000](http://127.0.0.1:8000)
- Registration: [http://127.0.0.1:8000/register](http://127.0.0.1:8000/register)
- Login: [http://127.0.0.1:8000/login](http://127.0.0.1:8000/login)
- API health check: [http://127.0.0.1:8000/api/health](http://127.0.0.1:8000/api/health)

The dashboard requires authentication. Register an account or sign in to use it.

### Rebuild after changing application code

Application source is copied into the Docker image rather than bind-mounted. Rebuild the app image after changing PHP or frontend source files:

    docker compose up -d --build app

## Configure local AI with Ollama

TaskFlow's default Compose configuration expects Ollama to run on the **host machine**, reachable from the app container at http://host.docker.internal:11434.

1. Install and start Ollama from [ollama.com](https://ollama.com/).
2. Download the default model on the host:

       ollama pull llama3.2:latest

3. Confirm the model is available:

       ollama list

4. Start or rebuild TaskFlow, then open the authenticated dashboard and try the AI task proposal form.

The default settings in compose.yaml are:

| Variable | Default | Purpose |
|---|---|---|
| OLLAMA_BASE_URL | http://host.docker.internal:11434 | Ollama base URL as seen from the container |
| OLLAMA_MODEL | llama3.2:latest | Model used to create proposals |
| OLLAMA_TIMEOUT | 60 | Request timeout in seconds |

If you change these values in compose.yaml, recreate the app container to apply them. Make sure the selected model has been pulled and that the Ollama service is reachable from Docker. AI generation may take time depending on your hardware and model.

**Privacy note:** prompts are sent to the Ollama service configured for your environment. With the default setup, the model runs locally on your host; this is not a guarantee that every custom Ollama endpoint is local.

## Use the dashboard

1. Register or sign in.
2. Review the dashboard and task statistics.
3. Create and manage tasks using the task interface.
4. Enter a natural-language request into the AI proposal form.
5. Review the proposed title and description.
6. Approve the proposal to save it, or discard it to leave the task list unchanged.

The proposal endpoint only generates a draft. The task is saved through the task-creation workflow after you approve it.

## API reference

Local API base URL:

    http://127.0.0.1:8000/api

### Public endpoints

| Method | Endpoint | Purpose |
|---|---|---|
| GET | /health | Check API health |
| POST | /register | Create an account and receive a token |
| POST | /login | Authenticate and receive a token |

Registration and login are rate-limited.

### Authenticated endpoints

Send the access token returned by registration or login in the Authorization header:

    Authorization: Bearer YOUR_ACCESS_TOKEN
    Accept: application/json

| Method | Endpoint | Purpose |
|---|---|---|
| POST | /logout | Revoke the current access token |
| GET | /user | Get the authenticated user's safe profile |
| GET | /tasks | List the authenticated user's tasks |
| POST | /tasks | Create a task |
| PUT | /tasks/{task} | Update an owned task |
| DELETE | /tasks/{task} | Delete an owned task |

All task endpoints require authentication. A user cannot access another user's tasks.

### Register an account

    POST /api/register
    Content-Type: application/json
    Accept: application/json

Request body:

    {
      "name": "Alex Developer",
      "email": "alex@example.com",
      "password": "UseAUniquePassword123!",
      "password_confirmation": "UseAUniquePassword123!"
    }

A successful registration returns a user representation and an access token. Keep the token private and do not include it in screenshots, logs, or source control.

### Log in

    POST /api/login
    Content-Type: application/json
    Accept: application/json

    {
      "email": "alex@example.com",
      "password": "UseYourPasswordHere"
    }

Use the returned token as a bearer token for protected API requests.

### Create a task

    POST /api/tasks
    Authorization: Bearer YOUR_ACCESS_TOKEN
    Content-Type: application/json
    Accept: application/json

    {
      "title": "Finish API documentation",
      "description": "Document endpoints and test the examples.",
      "status": "pending"
    }

New tasks default to pending when no status is supplied.

### List, search, filter, sort, and paginate

The GET /api/tasks endpoint supports query parameters:

| Parameter | Example | Purpose |
|---|---|---|
| search | ?search=laravel | Partial, case-insensitive search across title and description |
| status | ?status=in_progress | Filter by task status |
| sort | ?sort=oldest | Select a supported sort order |
| page | ?page=2 | Select a results page |
| per_page | ?per_page=10 | Set the page size within the allowed range |

Example with combined parameters:

    GET /api/tasks?search=laravel&status=completed&sort=oldest&page=1&per_page=10

Responses include pagination metadata such as current_page, per_page, total, and last_page. Invalid query options return validation errors.

### Update or delete a task

Use the task ID returned by the API and include your bearer token:

    PUT /api/tasks/123
    Authorization: Bearer YOUR_ACCESS_TOKEN
    Content-Type: application/json
    Accept: application/json

    {
      "title": "Finish the revised API documentation",
      "description": "Review the examples and verify them against the API."
    }

Replace 123 with an existing task ID belonging to your account. The API enforces task ownership.

### AI proposals in the browser

AI proposal generation is an authenticated **web** endpoint, not one of the public API endpoints listed above:

    POST /dashboard/ai/task-proposals
    Content-Type: application/json
    X-CSRF-TOKEN: YOUR_CSRF_TOKEN

The request body contains a prompt string (3–2000 characters). The endpoint is rate-limited and returns a proposal for review; it does not create a task itself. In the browser, use the dashboard form so the session and CSRF token are handled correctly.

## Run the tests

Run the full Pest suite inside the app container:

    docker compose exec app sh -lc 'touch .env && APP_ENV=testing ./vendor/bin/pest'

The current project test suite was verified with **57 passing tests and 225 assertions**. Results can change as the code and tests evolve; rerun the command to verify your checkout.

Coverage includes:

- Registration, login, logout, and rate limiting
- Protected API endpoints and safe user-profile responses
- Task creation, updates, deletion, and validation
- Task ownership and isolation between users
- Search, partial/case-insensitive matching, and description search
- Status filtering, sorting, pagination, and invalid query parameters
- Browser registration and task creation
- AI proposal validation and provider failure handling
- Missing, null, and empty AI descriptions receiving fallback text
- Ensuring proposal generation does not save a task before approval

## Security and reliability

- Keep .env, application keys, passwords, and bearer tokens out of Git.
- Use HTTPS and production-appropriate environment settings when deploying.
- Do not run with APP_DEBUG=true in production.
- Keep authenticated routes protected by middleware and enforce task ownership on the server.
- Treat AI output as untrusted input; validate it before presenting or saving it.
- Keep AI generation rate limits in place and handle provider failures gracefully.
- Review database backups, logging, rate limits, and deployment configuration before production use.

This repository is a development project. The passing automated tests and successful local workflow are useful verification, but they do not replace a full security review or production deployment checklist.

## Project structure

    app/
      Http/
        Controllers/
          Api/                 # API endpoints
          Web/                 # Browser-facing controllers
        Requests/               # Request validation, where applicable
        Resources/              # API resource responses
      Models/                   # User and Task models
      Policies/                 # Task authorization
      Services/
        AiTaskProposalService.php
    routes/
      api.php                   # JSON API routes
      web.php                   # Dashboard, auth, and AI proposal routes
    resources/
      js/                       # Dashboard interactions
      views/                    # Blade templates
    tests/
      Feature/                  # API, web, authorization, and AI behavior tests
    compose.yaml                # App and PostgreSQL services
    Dockerfile                  # Container image build

## Roadmap

Possible future improvements:

- Publish a machine-readable OpenAPI specification.
- Add a GitHub Actions workflow to run tests on pushes and pull requests.
- Add real screenshots or a short demo GIF of the dashboard and AI approval flow.
- Expand deployment documentation and operational health checks.
- Continue expanding authorization and AI error-path tests.

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
