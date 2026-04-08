## 2024-05-24 - Conditional Aggregation Optimization
**Learning:** Using `whereDate()` in Laravel prevents the database from using indices. Using conditional aggregation (`SUM(CASE WHEN...)`) inside `selectRaw()` combined with range queries (`>=`) instead of `whereDate()` reduces the number of queries and leverages database indexing effectively.
**Action:** When calculating statistics for different time periods (e.g., today vs month, today vs yesterday), use conditional aggregation in a single query with range conditions rather than executing separate queries using `whereDate()`.
