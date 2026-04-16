
## 2024-04-16 - Safe Database Optimization

**Learning:** When attempting to test database optimization using dummy binary artifacts, be careful that those binary artifacts aren't staged. Removing intermediate testing artifacts is standard but explicitly un-staging or ensuring `.gitignore` captures binaries is important. Furthermore, `selectRaw` with conditional aggregation (`CASE WHEN`) is a heavily preferred pattern over multiple `.where()` counts/sums to combat N+1 patterns without sacrificing readability.

**Action:** Before any `git status` check, ensure a thorough `rm` or `git restore` is utilized to clean up all dummy files generated during local database verifications to avoid polluting the repository with arbitrary binary or config files.
