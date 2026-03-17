<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Partner;
use App\Models\BoxTemplate;
use App\Models\ShopSession;
use App\Models\BoxOrder;
use App\Models\BoxOrderItem;
use App\Models\DailyConsignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class TrendSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Preparation
        $admin = $this->getOrCreateAdmin();
        $partners = $this->getOrCreatePartners();
        $boxTemplates = $this->getOrCreateBoxTemplates();

        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(30);

        // 2. Date Iteration
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            
            // Open Shop Session (08:00 AM)
            $openedAt = $date->copy()->setTime(8, 0, 0);
            $session = ShopSession::create([
                'user_id' => $admin->id,
                'opened_at' => $openedAt,
                'opening_cash' => 500000, // Rp 500.000 early cash
                'status' => 'open',
                'created_at' => $openedAt,
                'updated_at' => $openedAt,
            ]);

            $totalIncome = 0;

            // Generate Box Orders (10-20 per day)
            $numOrders = rand(10, 20);
            for ($i = 0; $i < $numOrders; $i++) {
                $statusRoll = rand(1, 100);
                if ($statusRoll <= 80) {
                    $status = 'completed';
                    $paymentStatus = 'paid';
                } elseif ($statusRoll <= 90) {
                    $status = 'pending';
                    $paymentStatus = 'unpaid';
                } else {
                    $status = 'cancelled';
                    $paymentStatus = 'unpaid';
                }

                $orderTime = $openedAt->copy()->addMinutes(rand(10, 800)); // order happens between 08:10 and 21:20

                $order = BoxOrder::create([
                    'customer_name' => 'Customer ' . rand(1000, 9999),
                    'box_template_id' => $boxTemplates->random()->id,
                    'quantity' => 1,
                    'total_price' => 0, // calculated later
                    'payment_method' => ['cash', 'qris', 'transfer'][array_rand(['cash', 'qris', 'transfer'])],
                    'payment_status' => $paymentStatus,
                    'pickup_datetime' => $orderTime->copy()->addHours(2),
                    'status' => $status,
                    'cancellation_reason' => $status === 'cancelled' ? 'Customer cancelled' : null,
                    'created_at' => $orderTime,
                    'updated_at' => $status === 'completed' ? $orderTime->copy()->addMinutes(10) : $orderTime,
                ]);

                // Attach Items
                $numItems = rand(1, 3);
                $orderTotal = 0;
                $orderItemsTemplates = $boxTemplates->random($numItems);

                foreach ($orderItemsTemplates as $itemTemplate) {
                    $qty = rand(1, 4);
                    $price = $itemTemplate->price;
                    $subtotal = $qty * $price;

                    BoxOrderItem::create([
                        'box_order_id' => $order->id,
                        'product_name' => $itemTemplate->name,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'subtotal' => $subtotal,
                        'created_at' => $orderTime,
                        'updated_at' => $orderTime,
                    ]);

                    $orderTotal += $subtotal;
                }

                $order->update(['total_price' => $orderTotal]);

                if ($status === 'completed') {
                    $totalIncome += $orderTotal;
                }
            }

            // Generate Consignments
            $consignmentTime = $date->copy()->setTime(21, 30, 0);
            foreach ($partners as $partner) {
                // Not all partners sell every day
                if (rand(1, 100) > 80) continue;

                $initialQty = rand(10, 30);
                $soldQty = rand(0, $initialQty);
                $basePrice = rand(5, 15) * 1000;
                $markupPercent = 20;
                $sellingPrice = $basePrice + ($basePrice * $markupPercent / 100);
                
                $subtotalIncome = $soldQty * $sellingPrice;
                $totalIncome += $subtotalIncome;

                DailyConsignment::create([
                    'shop_session_id' => $session->id,
                    'partner_id' => $partner->id,
                    'product_name' => $partner->name . ' Product ' . rand(1, 5),
                    'qty_initial' => $initialQty,
                    'qty_sold' => $soldQty,
                    'qty_remaining' => $initialQty - $soldQty,
                    'base_price' => $basePrice,
                    'selling_price' => $sellingPrice,
                    'markup_percent' => $markupPercent,
                    'subtotal_income' => $subtotalIncome,
                    'created_at' => $consignmentTime,
                    'updated_at' => $consignmentTime,
                ]);
            }

            // Close Shop Session (22:00 PM)
            $closedAt = $date->copy()->setTime(22, 0, 0);
            $session->update([
                'status' => 'closed',
                'closed_at' => $closedAt,
                'closing_cash_system' => 500000 + $totalIncome,
                'closing_cash_actual' => 500000 + $totalIncome + rand(-50000, 50000), // simulate slight cash differences
                'updated_at' => $closedAt,
            ]);
        }

        // 3. Command Instruction
        // php artisan app:aggregate-daily-stats
    }

    private function getOrCreateAdmin()
    {
        $admin = User::where('email', 'admin_trend@example.com')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Admin Trend',
                'email' => 'admin_trend@example.com',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]);
        }
        return $admin;
    }

    private function getOrCreatePartners()
    {
        if (Partner::count() < 3) {
            for ($i = 1; $i <= 3; $i++) {
                Partner::create([
                    'name' => 'Trend Partner ' . $i,
                    'phone' => '08123456789' . $i,
                    'address' => 'Test Address ' . $i,
                    'is_active' => true,
                ]);
            }
        }
        return Partner::all();
    }

    private function getOrCreateBoxTemplates()
    {
        if (BoxTemplate::count() < 5) {
            for ($i = 1; $i <= 5; $i++) {
                BoxTemplate::create([
                    'name' => 'Trend Template ' . $i,
                    'type' => $i % 2 === 0 ? 'heavy_meal' : 'snack_box',
                    'price' => rand(15, 50) * 1000,
                    'items_json' => json_encode(['Item A', 'Item B']),
                    'is_active' => true,
                ]);
            }
        }
        return BoxTemplate::all();
    }
}