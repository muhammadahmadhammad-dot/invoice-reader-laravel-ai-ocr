<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'user_id',
        'number',
        'vendor_name',
        'date',
        'total_amount',
        'remarks'
    ];
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
