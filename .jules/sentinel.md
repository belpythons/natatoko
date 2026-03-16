## 2024-05-23 - Unverified Webhook Endpoint (Spoofing Risk)
**Vulnerability:** The endpoint `/webhook/mayar` blindly accepts incoming requests and marks orders as `paid` if `status === 'SUCCESS'`. There is NO signature validation (HMAC) to ensure the request actually comes from Mayar.id. Anyone can hit this endpoint and mark their orders as paid.
**Learning:** External webhook endpoints require strict authentication. Relying purely on the structure of the JSON payload is insufficient because payloads can easily be spoofed by attackers.
**Prevention:** Always implement Webhook Signature verification checking the headers (e.g., checking `x-mayar-signature` against `config('services.mayar.webhook_secret')` using `hash_hmac`). If it fails, return 401 Unauthorized.
