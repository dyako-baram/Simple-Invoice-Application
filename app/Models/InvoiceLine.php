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
}
