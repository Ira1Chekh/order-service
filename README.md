# Product & Order Microservices

Two Symfony 6.4 services that talk to each other through RabbitMQ. The product service handles your product catalogue, the order service handles orders and keeps a local copy of products so it can validate stock without calling the product service directly.

## How it works

When you create a product, the product service saves it and publishes a message to RabbitMQ. The order service picks that up and stores the product locally. When you place an order, the order service checks its local copy, locks the row, verifies there's enough stock, and decrements the quantity — all in one transaction so you can't oversell.

```
product-service :8001          RabbitMQ              order-service :8002
       │                                                      │
  POST /products  ──── ProductDTO ──────────────────►  local_products table
                                                              │
                                                       POST /orders
                                                       locks product row
                                                       checks stock
                                                       saves order
                                                       ──── OrderDTO ──►
```

## Requirements

- Docker and Docker Compose

## Environment setup

Copy the example files and fill in your values:

```bash
# Docker Compose (required to run the stack)
cp .env.example .env

# Each service (only needed when running PHP outside Docker)
cp order-service/.env.example order-service/.env
cp product-service/.env.example product-service/.env
```

When running via Docker Compose, the root `.env` is the only file you need — it injects all variables into the containers. The service-level `.env` files are only used if you run Symfony directly on your machine without Docker.

## Getting started

```bash
docker compose up --build
```

That's it. Both services install their dependencies, run migrations, and start up automatically. The RabbitMQ consumer in the order service also starts in the background — you don't need to run anything manually.

The first build takes a few minutes because it downloads and compiles PHP extensions. Subsequent builds use the cache and are much faster.

## API

### Products — `http://localhost:8001`

**Create a product**
```bash
curl -X POST http://localhost:8001/products \
  -H 'Content-Type: application/json' \
  -d '{"name": "Coffee Mug", "price": 12.99, "quantity": 100}'
```

**List all products**
```bash
curl http://localhost:8001/products
```

**Get a single product**
```bash
curl http://localhost:8001/products/{id}
```

---

### Orders — `http://localhost:8002`

Before placing an order, create a product first and wait a second or two for it to sync over RabbitMQ.

**Place an order**
```bash
curl -X POST http://localhost:8002/orders \
  -H 'Content-Type: application/json' \
  -d '{"productId": "{id}", "customerName": "John Doe", "quantityOrdered": 2}'
```

The `quantity` in the product object is the remaining stock after the order. `orderStatus` will be `Processing` in the immediate response and flips to `Success` asynchronously once the `OrderCompletionHandler` processes the RabbitMQ message.

**List all orders**
```bash
curl http://localhost:8002/orders
```

**Get a single order**
```bash
curl http://localhost:8002/orders/{id}
```

**Error responses**

| Status | When |
|--------|------|
| `400`  | Missing or invalid fields |
| `404`  | Product not found (not synced yet, or wrong ID) |
| `422`  | Not enough stock |

## Testing the flow in Postman

1. `POST http://localhost:8001/products` with `{"name": "Coffee Mug", "price": 12.99, "quantity": 10}` — copy the `id` from the response
2. Wait 1–2 seconds for the order service to sync the product
3. `POST http://localhost:8002/orders` with `{"productId": "<id>", "customerName": "John Doe", "quantityOrdered": 3}` — `orderStatus` will be `Processing`; `GET` the order a moment later and it should be `Success`
4. Try ordering 20 — you should get a `422` with an insufficient stock message
5. `GET http://localhost:8002/orders` to see all orders

Optional: set up Postman environment variables `product_url=http://localhost:8001` and `order_url=http://localhost:8002` to avoid repeating the URLs.

## Unit tests

Tests are in the order-service. With the stack running:

```bash
docker compose exec order-app php bin/phpunit
```

Without a running container:

```bash
docker compose run --rm order-app php bin/phpunit
```

Run a specific file:

```bash
docker compose exec order-app php bin/phpunit tests/MessageHandler/ProductUpdateHandlerTest.php
```

## Useful commands

```bash
# Follow logs for a service
docker compose logs -f order-app
docker compose logs -f product-app

# Check migration status
docker compose exec order-app php bin/console doctrine:migrations:status
docker compose exec product-app php bin/console doctrine:migrations:status

# Watch the RabbitMQ consumer live
docker compose exec -e APP_ENV=prod order-app php bin/console messenger:consume product_updates order_events -vv

# Tear everything down including database volumes
docker compose down -v
```

## RabbitMQ UI

`http://localhost:15672` — username `guest`, password `guest`

Useful for checking whether messages are queued or have been consumed.

## Troubleshooting

**Order returns 404 for a product I just created** — the consumer needs a moment to process the RabbitMQ message. Wait a couple of seconds and try again. If it keeps failing, check `docker compose logs order-app` to make sure the consumer started.

**`TraceableEventDispatcher` warning when running the consumer manually** — always use `-e APP_ENV=prod` with `docker compose exec`. Without it Symfony runs in dev mode and a PHP 8 warning immediately kills the worker.

**502 Bad Gateway** — php-fpm is probably still starting up. Give it a few seconds.

**Database connection refused** — the DB container might not be healthy yet. Run `docker compose ps` and wait until the database shows as `healthy`.
