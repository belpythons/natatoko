
## 2024-05-24 - Do not optimize Memory by breaking API Contracts
**Learning:** Optimizing a method's database queries by returning empty collections instead of expected models in a structured array causes backward compatibility issues for other consumers and breaks the contract. Furthermore, returning the empty collection and then still executing the original eager-loaded models query to preserve compatibility negates any memory performance optimization, creating a net-negative effect.
**Action:** When optimizing methods returning collections, focus on the database level aggregations first if we don't need the models. If consumers still expect the models for backward compatibility, look for redundancies higher up the call stack (e.g. redundant caller method invocations) or combine similar DB aggregates to save query latency, rather than breaking the schema.

## 2024-05-24 - Conditional Aggregations to Combine Queries
**Learning:** Having 4 separate database queries for daily/monthly totals (e.g. `BoxOrder::whereDate(today)->count()`, `BoxOrder::whereDate(today)->sum()`, `BoxOrder::where(month)->count()`, etc.) causes 4 database hits, which can become a bottleneck.
**Action:** Combine them into a single `selectRaw()` query using `SUM(CASE WHEN...)` or `COUNT(CASE WHEN...)` based on the largest date range.
