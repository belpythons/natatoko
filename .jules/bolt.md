## 2026-04-06 - Bounding Aggregate Queries
**Learning:** When converting multiple queries into a single conditional aggregate query (e.g., `SUM(CASE WHEN...)`), applying a root-level `orWhere` causes the database to evaluate it globally, bypassing Global Scopes (like `SoftDeletes`) and potentially returning leaked records.
**Action:** Always enclose `orWhere` conditions inside a logically grouped closure (`$query->where(function ($q) {...})`) to ensure safe bounding.
