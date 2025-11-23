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
Method	              Endpoint	                Description
GET	              /api/test-requests	          List all requests
GET	              /api/test-requests/{id}	      Get details of a request
POST	            /api/test-requests	          Create a new request
PATCH	            /api/test-requests/{id}	      Update priority or status
DELETE	          /api/test-requests/{id}	      Delete a request (optional)

Test Runs endpoints will be added later

### 6. Architecture

This project follows a layered structure:

Controller → Input layer (HTTP)
Service → Business logic (planned)
Repository → Data access using Doctrine
This structure allows unit testing, clean refactoring and scalability.

### 7. Authentication (planned)

Authentication will be implemented via:

LexikJWTAuthenticationBundle

Role-based access:
admin | technician | operator

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
