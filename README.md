# Skyway

A flight management REST API built with Laravel 13. Supports creating and updating flights with nested legs and segments, asynchronous processing via Laravel Horizon, and idempotent updates.

---

## Tech Stack

| Layer | Choice |
|---|---|
| PHP | 8.3+ |
| Framework | Laravel 13.x |
| Database | MySQL |
| Cache & Queue | Redis + Laravel Horizon |
| Testing | Pest 4 + PHPUnit 12 |
| Static analysis | PHPStan 2 + Larastan 3 (level 8) |
| Code style | Laravel Pint |
| Local dev | Laravel Sail (Docker) |

---

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed and running.
- No local PHP, MySQL, or Redis installation required - Sail provides all services via Docker.

---

## Local Setup

### 1. Clone and install dependencies

If you have PHP installed locally:

```bash
git clone git@github.com:erdiarikan/skyway.git
cd skyway
composer install
```

If you do **not** have PHP installed locally, use the Docker helper to run Composer:

```bash
git clone git@github.com:erdiarikan/skyway.git
cd skyway
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

### 2. Configure the environment

```bash
cp .env.example .env
```

The default `.env.example` is pre-configured for Sail. Verify or set these values:

```env
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=skyway
DB_USERNAME=sail
DB_PASSWORD=password

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

QUEUE_CONNECTION=redis

API_KEY=your-secret-key
```

### 3. Start Sail

```bash
./vendor/bin/sail up -d
```

Sail starts MySQL, Redis, the PHP application container, and a dedicated Horizon worker. On first run, Docker pulls the images - this takes a few minutes.

### 4. Generate app key and run migrations

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

The app is now available at **`http://localhost`**. The Horizon dashboard is at **`http://localhost/horizon`**.

---

## Daily Commands

All commands run inside the Sail container via `./vendor/bin/sail`.

```bash
# Static analysis (PHPStan level 8 + Larastan)
./vendor/bin/sail php vendor/bin/phpstan analyse --memory-limit=2G

# Code style (Laravel Pint)
./vendor/bin/sail exec laravel.test vendor/bin/pint --dirty

# Start containers
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail down

# Run tests
./vendor/bin/sail artisan test --compact

# Filter to a specific test
./vendor/bin/sail artisan test --compact --filter=CreateFlightTest

# Format PHP code (Pint)
./vendor/bin/sail exec laravel.test vendor/bin/pint

# Tail logs
./vendor/bin/sail artisan pail

# Open a Tinker shell
./vendor/bin/sail artisan tinker
```

### Shell alias (optional)

Add to your `~/.zshrc` or `~/.bashrc` to avoid typing the full path:

```bash
alias sail='./vendor/bin/sail'
```

Then use `sail up -d`, `sail artisan migrate`, etc.

---

## Testing

```bash
./vendor/bin/sail artisan test --compact
```

Tests run against a dedicated `skyway_test` MySQL database (configured in `phpunit.xml`).

---

## API Endpoints

All endpoints require the `Api-Key` header for authentication.

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/flights` | Create a flight with legs and segments |
| `PUT` | `/api/flights/{flightId}` | Update an existing flight (async, idempotent) |
| `GET` | `/api/flights/{flightId}` | Retrieve a flight's legs and segments |

### Create Flight

```http
POST /api/flights
Api-Key: your-secret-key
Content-Type: application/json

{
  "legs": [
    {
      "segments": [
        {
          "origin": "BCN",
          "destination": "LON",
          "departure": "2026-06-09T06:45:00",
          "arrival": "2026-06-09T10:55:00",
          "cabinClass": "Y",
          "airline": "UA",
          "flightNumber": "101"
        }
      ]
    }
  ]
}
```

Response: `201 Created`

```json
{
  "flightId": "550e8400-e29b-41d4-a716-446655440000",
  "legs": [
    {
      "segments": [
        {
          "origin": "BCN",
          "destination": "LON",
          "departure": "2026-06-09T06:45:00",
          "arrival": "2026-06-09T10:55:00",
          "cabinClass": "Y",
          "airline": "UA",
          "flightNumber": "101"
        }
      ]
    }
  ]
}
```

### Update Flight

Accepts partial updates (one or more legs). Processed asynchronously via queue.

```http
PUT /api/flights/{flightId}
Api-Key: your-secret-key
Idempotency-Key: unique-request-id
Content-Type: application/json
```

Response: `202 Accepted`

```json
{ "flightId": "550e8400-e29b-41d4-a716-446655440000" }
```

Processing happens asynchronously via Horizon.

The `Idempotency-Key` header ensures duplicate requests (retries, network timeouts) are processed only once.

### Get Flight

```http
GET /api/flights/{flightId}
Api-Key: your-secret-key
```

Response: `200 OK` with the flight's legs and segments.

---

## Design Decisions

### Leg matching on update

The Update Flight endpoint receives legs without explicit identifiers. To determine which existing leg a payload leg corresponds to, the system matches by the leg's **route**: the `origin` of the first segment and the `destination` of the last segment.

This decision was made under the assumption that the assignment payload format is fixed — legs carry no `legId`. The assumption also holds that this endpoint handles **schedule changes** (departure/arrival times, flight numbers), not route changes. A leg's route (e.g. BCN→JFK) is treated as immutable identity.

**Known limitation:** if an incoming leg's effective origin or destination differs from any stored leg, the update returns `404`. The system cannot match a leg whose route has fundamentally changed.

**Alternative considered:** adding a `uuid` to each leg and including `legId` in the update payload. This would make matching explicit, eliminate the route-based business logic entirely, and handle route changes gracefully. It was not implemented because the assignment payload example carries no leg identifier, and the stated scope (schedule synchronisation) does not require route changes.

### Horizon dashboard authentication

In a production setup, the Horizon dashboard at `/horizon` would be protected by a gate in `App\Providers\HorizonServiceProvider` — typically restricting access to a set of known admin email addresses or a role check. This project leaves the gate empty (dashboard accessible only in local environments, blocked in production by default) because the assignment does not require an authenticated user system. Adding it is a one-line change inside `HorizonServiceProvider::gate()`.

### API versioning

In a production API, all routes would be prefixed with a version segment (e.g. `/api/v1/flights`) so that breaking changes can be introduced under `/api/v2/` without affecting existing consumers. This project omits versioning because the assignment specifies `/api/flights` as the canonical path and the scope does not include a second version. Adding it would be a one-line change in `routes/api.php`.

---

## License

MIT
