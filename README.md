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

Sail starts MySQL, Redis, and the PHP application container. On first run, Docker pulls the images - this takes a few minutes.

### 4. Generate app key and run migrations

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

The app is now available at **`http://localhost`**.

### 5. Start Horizon

In a separate terminal:

```bash
./vendor/bin/sail artisan horizon
```

Horizon processes queued flight update jobs. The dashboard is available at **`http://localhost/horizon`**.

---

## Daily Commands

All commands run inside the Sail container via `./vendor/bin/sail`.

```bash
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

Response: `201 Created` with `{ "flightId": "uuid" }`

### Update Flight

Accepts partial updates (one or more legs). Processed asynchronously via queue.

```http
PUT /api/flights/{flightId}
Api-Key: your-secret-key
Idempotency-Key: unique-request-id
Content-Type: application/json
```

Response: `204 No Content`

The `Idempotency-Key` header ensures duplicate requests (retries, network timeouts) are processed only once.

### Get Flight

```http
GET /api/flights/{flightId}
Api-Key: your-secret-key
```

Response: `200 OK` with the flight's legs and segments.

---

## License

MIT
