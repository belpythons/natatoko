<?php

namespace Tests\Benchmarks;

use App\Models\DailyConsignment;
use App\Models\Partner;
use App\Models\ShopSession;
use App\Models\User;
use App\Services\ConsignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsignmentBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    protected ConsignmentService $service;
    protected $session;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ConsignmentService();

        $user = User::factory()->create();
        $this->session = ShopSession::create([
            'user_id' => $user->id,
            'status' => 'open',
            'opened_at' => now(),
            'opening_cash' => 1000,
        ]);

        $partner = Partner::create([
            'name' => 'Test Partner',
            'is_active' => true,
        ]);

        // Create 100 consignments
        for ($i = 0; $i < 100; $i++) {
            DailyConsignment::create([
                'shop_session_id' => $this->session->id,
                'partner_id' => $partner->id,
                'product_name' => "Product $i",
                'qty_initial' => 10,
                'qty_sold' => 0,
                'qty_remaining' => 10,
                'base_price' => 100,
                'selling_price' => 150,
                'subtotal_income' => 0,
            ]);
        }
    }

    public function test_bulk_update_remaining_quantities_performance()
    {
        $consignments = DailyConsignment::all();
        $items = $consignments->map(function ($c) {
            return [
                'id' => $c->id,
                'qty_remaining' => 5,
            ];
        })->toArray();

        $start = microtime(true);
        $this->service->bulkUpdateRemainingQuantities($items);
        $end = microtime(true);

        $duration = ($end - $start) * 1000;
        echo "\nBulk Update Remaining Quantities (100 items) took: " . number_format($duration, 2) . "ms\n";

        $this->assertTrue(true);
    }
}
