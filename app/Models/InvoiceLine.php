<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
    use HasFactory;
    protected $fillable = [
        'invoice_id','line_price','item_id', 'product_id','user_id', 'quantity', 'price', 'total',
    ];
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
