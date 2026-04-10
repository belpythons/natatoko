## 2024-05-20 - Database query optimization with SelectRaw
**Learning:** In Laravel, filtering, counting and summing collections inside PHP memory like `$orders->where('status', 'pending')->count()` and `$orders->sum('total_price')` is slow and loads the whole resultset in memory. Instead, doing a `SUM(CASE WHEN...)` inside a `selectRaw` enables executing multiple aggregate calculations inside a single database query.
**Action:** When working on reporting algorithms, try combining multiple `count()` and `sum()` logic onto a single db query to minimize database hits.
