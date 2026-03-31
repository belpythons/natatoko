## 2026-03-31 - [Optimize Multiple Count/Sum Queries with DB Select Raw]
**Learning:** When retrieving multiple aggregations like `COUNT` and `SUM` for the same model and conditions, Eloquent will by default dispatch separate queries. Instead of 4 queries to get count/sum for today and month respectively, use `selectRaw('COUNT(*) as count, SUM(CASE WHEN status IN (...) THEN value ELSE 0 END) as sum')` to reduce 4 database hits into 2.
**Action:** Use `selectRaw` to combine counts and conditional sums in scenarios where the base query conditions (e.g., date ranges) are identical.
