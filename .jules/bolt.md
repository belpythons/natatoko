## 2026-04-01 - [Laravel selectRaw optimization]
**Learning:** Using Laravel's `selectRaw` to combine multiple queries (`count`, `sum` with conditionals) is extremely efficient in decreasing roundtrips to the database when gathering stats for the dashboard.
**Action:** When extracting multiple counts or conditional sums over the same tables, group them in a single query via `selectRaw` with conditional aggregation (`SUM(CASE WHEN...)`) rather than running multiple `where()->count()` or `where()->sum()` queries.
