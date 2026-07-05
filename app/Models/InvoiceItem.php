<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'stock_id',
        'product_name',
        'qty',
        'price',
        'total'
    ];
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
