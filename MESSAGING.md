# Messaging

```
POST /products
  │
  ▼
ProductService::create()
  │ dispatches
  ▼
ProductDTO ──► [product.updates] ──► ProductUpdateHandler
                                       └─ creates or updates local_products row in order-service

POST /orders
  │
  ▼
OrderService::create()
  │ dispatches
  ▼
OrderDTO ──► [order.events] ──► OrderCompletionHandler (order-service)
                │                  └─ logs the completed order
                │
                └──────────────► OrderCompletionHandler (product-service)
                                   └─ decrements product quantity in products table
```

| Message | Published by | Queue | Handler | What it does |
|---------|-------------|-------|---------|--------------|
| `ProductDTO` | product-service | `product.updates` | `ProductUpdateHandler` | Syncs product into order-service `local_products` |
| `OrderDTO` | order-service | `order.events` | `OrderCompletionHandler` (order-service) | Logs the completed order |
| `OrderDTO` | order-service | `product_service.order_events` | `OrderCompletionHandler` (product-service) | Decrements stock in product-service `products` |
