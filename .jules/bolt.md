## 2025-04-09 - Conditional Aggregation Optimization
**Learning:** Multiple simple count/sum aggregate queries against the same table using different criteria bounds act as a performance bottleneck by producing unnecessary database round trips.
**Action:** Consolidate these into a single database hit using conditional aggregation inside a `selectRaw` block (`SUM(CASE WHEN ... THEN value ELSE 0 END)`). Ensure proper parameter binding and test execution time to confirm the impact.
