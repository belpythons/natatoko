## 2024-05-18 - [Preventing Sensitive Data Exposure in Logs]
**Vulnerability:** Found `Log::info('Mayar webhook received', $request->all());` in `app/Http/Controllers/Pos/BoxOrderController.php`, which dumps the entire request payload into application logs, potentially exposing sensitive customer and payment data.
**Learning:** Overusing `$request->all()` in logging statements is a common pattern that risks violating data privacy standards (like GDPR/PCI-DSS) by indiscriminately recording all input data.
**Prevention:** Always log specific, explicitly defined variables instead of entire request objects (e.g., logging only `transaction_id` and `status`).
