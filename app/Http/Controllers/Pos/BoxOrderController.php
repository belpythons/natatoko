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
        $user = auth()->user();
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
        $user = auth()->user();
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
}