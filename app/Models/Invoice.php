<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_unique_id', 'invoice_date', 'customer_id', 'invoice_total', 'user_id','tax_rate'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function invoiceLines()
    {
        return $this->hasMany(InvoiceLine::class);
    }
}
