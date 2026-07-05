<?php

namespace App\Service;

use App\Models\Invoice;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $invoice = Invoice::create([
                'user_id' => auth()->id(),
                'number' => $data['number'],
                'vendor_name' => $data['vendor_name'],
                'date' => $data['date'],
                'remarks' => $data['remarks'] ?? null,
                'total_amount' => 0
            ]);

            $total = 0;

            foreach ($data['items'] as $item) {

                $itemTotal = $item['qty'] * $item['price'];

                $stock = Stock::firstOrCreate(
                    ['product_name' => $item['product_name']],
                    ['current_stock' => 0]
                );

                $invoice->items()->create([
                    'stock_id' => $stock->id,
                    'product_name' => $item['product_name'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'total' => $itemTotal
                ]);

                $total += $itemTotal;

                // STOCK UPDATE
                $stock->update([
                    'current_stock' => $stock->current_stock +  $item['qty'],
                    'current_purchase_price' =>  $item['price'],
                ]);
            }

            $invoice->update(['total_amount' => $total]);

            return $invoice;
        });
    }
}
