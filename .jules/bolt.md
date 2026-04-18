## 2025-04-18 - [Optimizing BoxOrderStats aggregation]
**Learning:** Combining multiple small aggregates on the same table (e.g. `today_orders`, `today_revenue`, `month_orders`, etc.) into a single `selectRaw` query using `SUM(CASE WHEN...)` massively reduces the number of database queries and avoids redundant table scans. It brings multiple 5 queries into 1.
**Action:** Look for multiple aggregates performed on the same model/table in a row and combine them using `selectRaw` with conditional aggregation if the conditions differ, or regular `SUM`/`COUNT` if conditions are shared.
