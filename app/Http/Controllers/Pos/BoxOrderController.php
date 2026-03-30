<?php
/**
 * Created/Modified by: Nata Toko Team
 * Feature: Order Box - Controller untuk manajemen order box
 */
namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Events\OrderUpdated;
use App\Models\BoxOrder;
use App\Models\BoxTemplate;
use App\Services\AdminDataService;
use App\Services\BoxOrderService;
use App\Services\ShopSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BoxOrderController extends Controller
{
    public function __construct(
        protected BoxOrderService $boxOrderService,
        protected AdminDataService $adminDataService,
        protected ShopSessionService $shopSessionService
        )
    {
    }

    /**
     * Display box order listing with countdown timers.
     */
    public function index(): Response
    {
        $user = \App\Models\User::first();
        $activeSession = $this->shopSessionService->getActiveSession($user);

        $upcomingOrders = $this->boxOrderService->getUpcomingOrders();
        $todayOrders = $this->boxOrderService->getTodayOrders();

        return Inertia::render('Pos/Box/Index', [
            'upcomingOrders' => $upcomingOrders,
            'todayOrders' => $todayOrders,
            'hasActiveSession' => $activeSession !== null,
            'activeSession' => $activeSession,
        ]);
    }

    /**
     * Show form to create a new order (flexible line items).
     */
    public function create(BoxTemplate $template = null): Response
    {
        $user = \App\Models\User::first();
        $activeSession = $this->shopSessionService->getActiveSession($user);

        // Get all active box templates for dropdown
        $boxTemplates = $this->adminDataService->getBoxTemplates();

        return Inertia::render('Pos/Box/Create', [
            'selectedTemplate' => $template,
            'boxTemplates' => $boxTemplates,
            'hasActiveSession' => $activeSession !== null,
            'activeSession' => $activeSession,
        ]);
    }

    /**
     * Store a new box order with flexible line items.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'pickup_datetime' => 'required|date|after:now',
            'quantity' => 'required|integer|min:1',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $order = $this->boxOrderService->createOrder($validated);

            return redirect()
                ->route('pos.box.index')
                ->with('success', 'Order berhasil dibuat untuk ' . $order->customer_name);
        }
        catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Upload payment proof for an order.
     */
    public function uploadProof(Request $request, int $orderId): RedirectResponse
    {
        $validated = $request->validate([
            'payment_proof' => 'required|image|max:5120', // 5MB max
        ]);

        $order = BoxOrder::findOrFail($orderId);

        try {
            $this->boxOrderService->uploadPaymentProof($order, $request->file('payment_proof'));

            return redirect()
                ->back()
                ->with('success', 'Bukti pembayaran berhasil diupload.');
        }
        catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update order status with conditional validation.
     * - For paid/completed: requires payment_proof upload
     * - For cancelled: requires cancellation_reason
     */
    public function updateStatus(Request $request, BoxOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,paid,completed,cancelled',
            'payment_proof' => [
                Rule::requiredIf(
                in_array($request->input('status'), ['paid', 'completed'])
                && !$order->payment_proof_path
            ),
                'nullable', 'image', 'max:5120',
            ],
            'cancellation_reason' => [
                Rule::requiredIf($request->input('status') === 'cancelled'),
                'nullable', 'string', 'max:1000',
            ],
        ]);

        try {
            // Handle payment proof upload if provided
            if ($request->hasFile('payment_proof')) {
                $this->boxOrderService->uploadPaymentProof($order, $request->file('payment_proof'));
                $order->refresh();
            }

            // Handle cancellation reason
            if ($validated['status'] === 'cancelled' && !empty($validated['cancellation_reason'])) {
                $this->boxOrderService->cancelOrderWithReason($order, $validated['cancellation_reason']);
            }
            else {
                $this->boxOrderService->updateOrderStatus($order, $validated['status']);
            }
            // Broadcast real-time update via Reverb
            $order->refresh();
            event(new OrderUpdated($order));

            return redirect()
                ->back()
                ->with('success', 'Status order berhasil diperbarui.');
        }
        catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Download receipt PDF for an order.
     */
    public function downloadReceipt(BoxOrder $order)
    {
        return $this->boxOrderService->generateReceipt($order);
    }

    /**
     * Create a Mayar.id QRIS payment for a box order.
     */
    public function createMayarPayment(Request $request, BoxOrder $order)
    {
        try {
            $response = Http::withToken(config('services.mayar.api_key'))
                ->post(config('services.mayar.base_url') . '/v1/payment/create', [
                    'name' => $order->customer_name,
                    'amount' => (int) $order->total_price,
                    'description' => 'Order Box #' . $order->id . ' - ' . $order->customer_name,
                ]);

            if ($response->successful()) {
                $data = $response->json('data');

                $order->update([
                    'payment_method' => 'qris',
                    'payment_status' => 'pending',
                    'mayar_link' => $data['link'] ?? null,
                    'mayar_transaction_id' => $data['id'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'link' => $data['link'] ?? null,
                    'transaction_id' => $data['id'] ?? null,
                ]);
            }

            Log::error('Mayar payment creation failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pembayaran QRIS.',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Mayar payment exception', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat pembayaran.',
            ], 500);
        }
    }

    /**
     * Handle incoming Mayar.id webhook callback.
     */
    public function handleMayarWebhook(Request $request)
    {
        // Security Enhancement: Verify Webhook Signature
        // This prevents attackers from spoofing webhooks and marking orders as paid
        $signature = $request->header('x-mayar-signature');
        $secret = config('services.mayar.webhook_secret');

        if (!$signature || !$secret) {
            Log::warning('Mayar webhook missing signature or secret configured.');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $expectedSignature = hash_hmac('sha256', $request->getContent(), $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Mayar webhook invalid signature provided.', ['ip' => $request->ip()]);
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $transactionId = $request->input('data.id') ?? $request->input('transaction_id');
        $status = $request->input('data.status') ?? $request->input('status');

        // SECURITY FIX: Avoid logging $request->all() to prevent exposing sensitive customer/payment data
        Log::info('Mayar webhook received', [
            'transaction_id' => $transactionId,
            'status' => $status
        ]);

        if (!$transactionId) {
            return response()->json(['message' => 'No transaction ID provided.'], 400);
        }

        $order = BoxOrder::where('mayar_transaction_id', $transactionId)->first();

        if (!$order) {
            Log::warning('Mayar webhook: order not found', ['transaction_id' => $transactionId]);
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if (strtoupper($status) === 'SUCCESS') {
            $order->update(['payment_status' => 'paid']);
            Log::info('Mayar webhook: order paid', ['order_id' => $order->id]);
        }

        return response()->json(['message' => 'Webhook processed.'], 200);
    }

    /**
     * Check the current payment status of an order (for frontend polling).
     */
    public function checkPaymentStatus(BoxOrder $order)
    {
        return response()->json([
            'payment_status' => $order->payment_status,
        ]);
    }
}