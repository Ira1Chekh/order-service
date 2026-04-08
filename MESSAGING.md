# Messaging

```
POST /products
  │
  ▼
ProductService::create()
  │ dispatches
  ▼
ProductDTO ──► [product.updates] ──► ProductUpdateHandler
                                       └─ creates local_products row in order-service (initial sync only)

POST /orders
  │
  ▼
OrderService::create()
  │ saves order as Processing, then dispatches
  ▼
OrderDTO ──► [order.events] ──► OrderCompletionHandler (order-service)
                │                  └─ finds order by id, sets status to Success
                │
                └──────────────► OrderCompletionHandler (product-service)
                                   └─ decrements product quantity in products table
```

| Message | Published by | Queue | Handler | What it does |
|---------|-------------|-------|---------|--------------|
| `ProductDTO` | product-service | `product.updates` | `ProductUpdateHandler` | Syncs product into order-service `local_products` on first receipt; ignores redeliveries |
| `OrderDTO` | order-service | `order.events` | `OrderCompletionHandler` (order-service) | Sets order status from Processing to Success |
| `OrderDTO` | order-service | `product_service.order_events` | `OrderCompletionHandler` (product-service) | Decrements stock in product-service `products` |
