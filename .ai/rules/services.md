---
paths:
  - 'app/Services/**'
---

# Services

## Stock mutations only via StockService
Never mutate product stock directly (`$product->stock -= $qty`) in controllers or other services. All stock changes go through StockService::applyChange() so every mutation writes a stock_movement with an explicit source (PURCHASE_IN, SALE_OUT, ADJUSTMENT). Multi-step workflows (sale complete, receiving finalize) wrap create+stock+movement in one DB transaction; business validation (actual stock at transaction time) belongs in the Service, not the FormRequest.
