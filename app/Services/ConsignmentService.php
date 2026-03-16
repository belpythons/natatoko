<?php

namespace App\Services;

use App\Models\DailyConsignment;
use App\Models\ShopSession;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ConsignmentService
{
    /**
     * Add a new consignment item to a session.
     * Now accepts selling_price directly instead of calculating from markup.
     */
    public function addConsignment(ShopSession $session, array $data): DailyConsignment
    {
        if ($session->isClosed()) {
            throw new \Exception('Sesi sudah ditutup. Tidak dapat menambah item.');
        }

        $basePrice = (float) $data['base_price'];
        $sellingPrice = (float) $data['selling_price'];
        $qtyInitial = (int) $data['qty_initial'];

        // Validate selling price is not less than base price (optional business rule)
        if ($sellingPrice < $basePrice) {
            throw new \Exception('Harga jual tidak boleh kurang dari harga modal.');
        }

        return DailyConsignment::create([
            'shop_session_id' => $session->id,
            'partner_id' => $data['partner_id'],
            'product_name' => $data['product_name'],
            'qty_initial' => $qtyInitial,
            'qty_sold' => 0,
            'qty_remaining' => $qtyInitial, // Will be updated during close shop
            'base_price' => $basePrice,
            'selling_price' => $sellingPrice,
            'markup_percent' => 0, // No longer used, kept for compatibility
            'subtotal_income' => 0, // Will be calculated during close shop
        ]);
    }

    /**
     * Get consignments for a session.
     */
    public function getSessionConsignments(ShopSession $session): Collection
    {
        return $session->consignments()
            ->with('partner')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Update sold quantity for a consignment item.
     * Calculates qty_remaining = qty_initial - qty_sold
     */
    public function updateSoldQuantity(DailyConsignment $consignment, int $qtySold): DailyConsignment
    {
        // Validate qty_sold doesn't exceed qty_initial
        if ($qtySold > $consignment->qty_initial) {
            throw new \Exception('Jumlah terjual tidak boleh melebihi stok awal.');
        }

        if ($qtySold < 0) {
            throw new \Exception('Jumlah terjual tidak boleh negatif.');
        }

        $qtyRemaining = $consignment->qty_initial - $qtySold;
        $subtotalIncome = $qtySold * $consignment->selling_price;

        $consignment->update([
            'qty_sold' => $qtySold,
            'qty_remaining' => $qtyRemaining,
            'subtotal_income' => $subtotalIncome,
        ]);

        return $consignment->fresh();
    }

    /**
     * Update remaining quantity for a consignment item during close shop.
     * Calculates qty_sold = qty_initial - qty_remaining
     */
    public function updateRemainingQuantity(DailyConsignment $consignment, int $qtyRemaining): DailyConsignment
    {
        // Validate qty_remaining doesn't exceed qty_initial
        if ($qtyRemaining > $consignment->qty_initial) {
            throw new \Exception('Sisa stok tidak boleh melebihi stok awal.');
        }

        if ($qtyRemaining < 0) {
            throw new \Exception('Sisa stok tidak boleh negatif.');
        }

        $qtySold = $consignment->qty_initial - $qtyRemaining;
        $subtotalIncome = $qtySold * $consignment->selling_price;

        $consignment->update([
            'qty_sold' => $qtySold,
            'qty_remaining' => $qtyRemaining,
            'subtotal_income' => $subtotalIncome,
        ]);

        return $consignment->fresh();
    }

    /**
     * Bulk update sold quantities.
     */
    public function bulkUpdateSoldQuantities(array $items): array
    {
        $ids = array_column($items, 'id');
        $consignments = DailyConsignment::whereIn('id', $ids)->get()->keyBy('id');

        return DB::transaction(function () use ($items, $consignments) {
            $results = [];

            foreach ($items as $item) {
                $consignment = $consignments->get($item['id']);
                if ($consignment) {
                    $results[] = $this->updateSoldQuantity($consignment, (int) $item['qty_sold']);
                }
            }

            return $results;
        });
    }

    /**
     * Bulk update remaining quantities during close shop.
     */
    public function bulkUpdateRemainingQuantities(array $items): array
    {
        $ids = array_column($items, 'id');
        $consignments = DailyConsignment::whereIn('id', $ids)->get()->keyBy('id');

        return DB::transaction(function () use ($items, $consignments) {
            $results = [];

            foreach ($items as $item) {
                $consignment = $consignments->get($item['id']);
                if ($consignment) {
                    $results[] = $this->updateRemainingQuantity($consignment, (int) $item['qty_remaining']);
                }
            }

            return $results;
        });
    }

    /**
     * Get consignment summary for a session.
     */
    public function getSessionSummary(ShopSession $session): array
    {
        $consignments = $session->consignments;

        return [
            'total_items' => $consignments->count(),
            'total_qty_initial' => $consignments->sum('qty_initial'),
            'total_qty_sold' => $consignments->sum('qty_sold'),
            'total_qty_remaining' => $consignments->sum('qty_remaining'),
            'total_income' => $consignments->sum('subtotal_income'),
            'total_stock_value' => $consignments->sum(function ($c) {
                return $c->qty_initial * $c->selling_price;
            }),
            'total_profit' => $consignments->sum(function ($c) {
                return $c->qty_sold * ($c->selling_price - $c->base_price);
            }),
        ];
    }
}
