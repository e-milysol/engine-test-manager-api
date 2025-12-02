# Engine Test Manager API

REST API built with Symfony 6 to manage test requests and engine test executions.  
This project is a demo focused on backend architecture, maintainability and real-world scenarios.


##  Purpose

The goal of this project is to provide a clean, modular backend for managing:
**Test Requests**: creation, prioritization and lifecycle
**Test Runs**: execution records, results, notes, timestamps
**User Roles**: admin, technician, operator (future implementation)

This API is inspired by industrial environments where multiple teams coordinate validation tasks across different facilities.


##  Tech Stack

**PHP 8.2**
**Symfony 6.x**
**MySQL 8**
**Doctrine ORM**
**Nginx**
**Docker + Docker Compose**
**JWT Authentication (planned)**
**PHPUnit tests (planned)**


##  Installation

### 1. Clone the repository

git clone https://github.com/e-milysol/engine-test-manager-api.git
cd engine-test-manager-api

### 2. Start the environment
docker compose up -d

### 3. Execute commands inside the PHP container
docker exec -it etm-php bash

### 4. Create the database
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate

### 5. API Endpoints (initial MVP)
Test Requests
Method	              Endpoint	                	Description
GET	              	/api/test-requests	          List all requests
GET	              	/api/test-requests/{id}	      Get details of a request
POST	            /api/test-requests	          Create a new request
PATCH	            /api/test-requests/{id}	      Update priority or status
DELETE	          	/api/test-requests/{id}	      Delete a request (optional)

Test Runs endpoints will be added later

### 6. Architecture

This project follows a layered structure:

Controller → Input layer (HTTP)
Service → Business logic (planned)
Repository → Data access using Doctrine
This structure allows unit testing, clean refactoring and scalability.

### 7. Authentication  (JWT)

This API uses JWT tokens for authentication.

1. Obtain a token

POST /api/login

Body:

{
  "email": "admin@local.com",
  "password": "admin"
}


Response:

{
  "token": "eyJ0eXA...",
  "user": {
    "id": 1,
    "email": "admin@local.com",
    "roles": ["ROLE_USER"]
  }
}

2. Use the token in protected endpoints

Add the header:

Authorization: Bearer <your_token>

Example protected endpoint


GET /api/me

Returns user info if the token is valid:

{
  "id": 1,
  "email": "admin@local.com",
  "roles": ["ROLE_USER"]
}


If missing or invalid token → 401 Unauthorized.

### 8. Testing (planned)
PHPUnit test suite
API functional tests
Docker isolated test environment

### 9. Next Milestones
Add authentication (JWT)
Add role layers
Add TestRun entity endpoints
Add pagination and filtering
Add Swagger/OpenAPI documentation

### 10. Contributions
This repository is for demonstration purposes only.
Pull Requests are welcome for:
  Performance improvements
  Code quality
  Documentation
## 11. Domain model

The API exposes two main entities:

- **TestRequest**: high-level request for an engine or component test.
- **TestRun**: individual execution of a given TestRequest.

### 12. TestRequest

Fields:
- id
- title
- description
- status (`NEW`, `IN_PROGRESS`, `DONE`, `CANCELLED`)
- priority (`1`–`5`)
- createdAt, updatedAt

Example (JSON):

json
{
  "id": 2,
  "title": "Cold start validation",
  "description": "Verify cold start behaviour at -10ºC",
  "status": "IN_PROGRESS",
  "priority": 1,
  "createdAt": "...",
  "updatedAt": "..."
}

### 13. TestRun

Fields:

id
testRequest (foreign key)
result (PENDING, PASSED, FAILED, CANCELLED)
notes
startedAt
finishedAt

Example (JSON):

json
{
  "id": 1,
  "testRequest": 2,
  "result": "PASSED",
  "notes": "All parameters within expected range",
  "startedAt": "...",
  "finishedAt": "..."
}

Example endpoints

GET /api/test-requests
POST /api/test-requests
GET /api/test-requests/{id}
PATCH /api/test-requests/{id}
DELETE /api/test-requests/{id}
GET /api/test-requests/{id}/runs
POST /api/test-requests/{id}/runs
GET /api/test-runs/{id}
PATCH /api/test-runs/{id}

### 14. Business Endpoints

The API provides endpoints to manage engine test requests and runs.

List TestRuns for a TestRequest

GET /api/test-requests/{id}/test-runs

Create TestRun

POST /api/test-runs

Body:

{
  "testRequestId": 2,
  "result": "PENDING",
  "notes": "Initial test run"
}


Response:

{
  "id": 12,
  "testRequest": 2,
  "result": "PENDING",
  "notes": "Initial test run",
  "startedAt": "2025-11-24T19:20:08+00:00",
  "finishedAt": null
}

Get TestRun

GET /api/test-runs/{id}

Update TestRun

PATCH /api/test-runs/{id}

{
  "result": "PASSED",
  "notes": "Validated and confirmed",
  "finishedAt": "2025-11-24T21:10:00+00:00"
}