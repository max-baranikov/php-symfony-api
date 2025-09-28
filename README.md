# Products API (Symfony 7 + Docker + Postgres)

A small, production-leaning implementation of the **Products API** test. Focus: clean architecture (DDD-lite), testability, OpenAPI-first (optional), Dockerized.

---

## Table of contents

- [Products API (Symfony 7 + Docker + Postgres)](#products-api-symfony-7--docker--postgres)
  - [Table of contents](#table-of-contents)
  - [1. Overview](#1-overview)
  - [2. Tech stack](#2-tech-stack)
  - [3. Architecture](#3-architecture)
  - [4. Project structure](#4-project-structure)
  - [5. Running locally (Development)](#5-running-locally-development)
  - [6. Running in Production mode (TBD)](#6-running-in-production-mode-tbd)
  - [7. Configuration \& env vars](#7-configuration--env-vars)
  - [8. Database \& migrations](#8-database--migrations)
  - [9. Seeding data](#9-seeding-data)
  - [10. Linting \& static analysis](#10-linting--static-analysis)
  - [11. Tests (unit \& e2e)](#11-tests-unit--e2e)
  - [12. API documentation (Swagger / OpenAPI)](#12-api-documentation-swagger--openapi)
  - [13. OpenAPI-first code generation (TBD)](#13-openapi-first-code-generation-tbd)
  - [14. Performance notes](#14-performance-notes)
  - [15. Design decisions](#15-design-decisions)

---

## 1. Overview

**Endpoint:** `GET /products?category=...&priceLessThan=...`

- Returns up to **5** products with discounts applied.
- Filters are applied **before** discounts.
- Discount rules (for demo):
  - Category `boots`: **30%**.
  - SKU `000003`: **15%**.
  - If multiple apply → use the **max** discount.
- Prices are **integers (cents)**; output follows the model specified in the task.

---

## 2. Tech stack

- **Language/Framework:** PHP 8.3+, **Symfony 7.x**
- **DB:** Postgres 16 (Docker)
- **ORM:** Doctrine ORM + Migrations
- **Tests:** PHPUnit + Symfony Test (functional)
- **API Docs:** NelmioApiDocBundle (Swagger UI) + OpenAPI 3
- **Linters:** PHP-CS-Fixer, PHPStan (level 8) or Psalm (strict)
- **Containers:** Docker Compose (nginx + php-fpm + postgres)

---

## 3. Architecture

**DDD-lite (modular monolith)** with clear separation:

- **Domain/** — Entities/Value Objects, domain services, discount policies.
- **Application/** — Use cases (e.g., `ListProducts`), input DTOs, result transformers.
- **Infrastructure/** — Doctrine repositories, Symfony controllers, DI wiring, frameworks.
- **Presentation/** — HTTP request/response DTO mapping, serialization, error handling.

Discounts are applied **in application layer**, repositories return domain models; controllers map to API shape.

---

## 4. Project structure

```
./
├─ app/ (Symfony app root)
│  ├─ config/
│  │  ├─ packages/
│  │  ├─ routes/
│  │  └─ services.yaml
│  ├─ src/
│  │  ├─ Domain/
│  │  │  ├─ Product/
│  │  │  │  ├─ Product.php
│  │  │  │  ├─ Price.php
│  │  │  │  ├─ DiscountPolicy.php (interface)
│  │  │  │  └─ Policies/
│  │  │  │     ├─ BootsCategoryPolicy.php
│  │  │  │     └─ SkuPolicy.php
│  │  ├─ Application/
│  │  │  └─ ListProducts/
│  │  │     ├─ ListProductsQuery.php (category, priceLessThan)
│  │  │     ├─ ListProductsHandler.php
│  │  │     └─ ProductView.php (API-facing view model)
│  │  ├─ Infrastructure/
│  │  │  ├─ Persistence/
│  │  │  │  ├─ Doctrine/
│  │  │  │  │  ├─ ProductEntity.php
│  │  │  │  │  └─ ProductRepository.php (implements Domain interface)
│  │  │  ├─ Http/
│  │  │  │  ├─ Controller/
│  │  │  │  │  └─ ProductController.php (GET /products)
│  │  │  │  └─ ExceptionListener.php
│  │  │  └─ OpenApi/
│  │  └─ Presentation/
│  │     └─ Transformer/
│  │        └─ ProductToApiTransformer.php
│  ├─ tests/
│  │  ├─ Unit/
│  │  ├─ Functional/
│  │  └─ Fixtures/
│  ├─ migrations/
│  ├─ bin/console
│  └─ public/index.php
├─ docker/
│  ├─ php/Dockerfile
│  └─ nginx/Dockerfile
├─ docker-compose.yml
├─ Makefile
├─ openapi.yaml (TBD — OpenAPI-first)
└─ README.md
```

---

## 5. Running locally (Development)

**Requirements:** Docker & Docker Compose.

1) Run this command:

```bash
make setup
```

1) Try the endpoint

open http://localhost:8080/api/docs or call directly http://localhost:8080/api/products

1) Run tests

```bash
make test
```

**Ports**

- App (nginx): `:8080`
- Postgres: `:5432`

---

## 6. Running in Production mode (TBD)

TBD :)

- Use production Dockerfile stage (`APP_ENV=prod`), opcache enabled, `composer install --no-dev --optimize-autoloader`.
- Database migrations executed on release (`bin/console doctrine:migrations:migrate --no-interaction`).
- Read-only filesystem except for cache/logs (mounted to writable volumes or tmpfs).
- Healthcheck endpoint (nginx upstream) & container `HEALTHCHECK`.

```bash
make prod-up   # builds prod images and starts stack
```

---

## 7. Configuration & env vars

- `.env.dev` (dev defaults) / `.env` (overrides) / prod via real env vars.

Key variables:

```
APP_ENV=dev|prod
DATABASE_URL=postgresql://symfony:symfony@db:5432/app?serverVersion=16&charset=utf8
TRUSTED_PROXIES=127.0.0.1,REMOTE_ADDR
```

---

## 8. Database & migrations

- **Schema (minimal):**
  - `products(sku PK, name, category, price_cents)`
  - Indexes: `idx_products_category`, `idx_products_price`
- **Migrations:** Doctrine Migrations (`bin/console make:migration`)

---

## 9. Seeding data

- Command `bin/console app:seed` loads the 5 given products.
- Optional flag `--bulk=25000` to generate additional data for perf testing.

---

## 10. Linting & static analysis

Composer scripts & Makefile targets:

```bash
make pre-commit   # run all the checks, including tests
```



```bash
make cs-fix       # php-cs-fixer fix
make cs-check     # php-cs-fixer
make phpstan      # phpstan analyse --level=max
```

Pre-commit hook:

TBD :)

---

## 11. Tests (unit & e2e)

- **Unit:** domain (discount selection, rounding), transformer, repository contract with in-memory fake.
- **Functional/E2E:** Symfony `WebTestCase` hitting `GET /products` (in-memory kernel + SQLite or test container Postgres). No network/filesystem required for core business tests.

Run:

```bash
make test        # vendor/bin/phpunit
```

---

## 12. API documentation (Swagger / OpenAPI)

- **NelmioApiDocBundle** exposed at `/api/docs` (Swagger UI) and `/api/docs.json` (OpenAPI JSON).
- As it ignores some fields, there's also generated spec under `public/openapi.yaml`

---

## 13. OpenAPI-first code generation (TBD)

TBD :)

If you prefer to write `openapi.yaml` first and **generate Symfony server stubs**, use **OpenAPI Generator**.

**Example:**

```bash
# Generate a Symfony bundle from openapi.yaml into ./generated
docker run --rm -v ${PWD}:/local openapitools/openapi-generator-cli:v7.8.0 \
  generate -i /local/openapi.yaml -g php-symfony -o /local/generated \
  --additional-properties=packageName=PromotionsApiBundle

# Then, register the generated bundle and routes in your Symfony app
# (see generated README for exact steps), and implement handlers.
```

> In this repo we keep the main code hand-written (controllers/use cases) and **publish OpenAPI from code** via Nelmio. The OpenAPI-first flow above is provided as a potential future workflow.

---

## 14. Performance notes

- Filtering done in SQL (`WHERE category=? AND price_cents <= ?`) with indexes.
- Always `LIMIT 5` + stable ordering by `sku` for deterministic results.
- Discounts applied in memory on the 0–5 returned rows.
- Prices are stored/returned in **cents**; rounding: **banker’s rounding to nearest cent (half up)**.

---

## 15. Design decisions

- **DDD boundaries** keep domain pure and testable (no framework in domain).
- **Max discount wins** when multiple rules match; rules are composable and test-covered.
- **No pagination** per task; trivial to add later (`page`, `limit`, or cursor).
- **Docker-first** to guarantee one-command run for any reviewer.

---
