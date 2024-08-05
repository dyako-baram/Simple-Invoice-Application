<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Customer;
use App\Models\Product;
use DB;

class InvoiceController extends Controller
{
    public function index()
    {
        return Invoice::with('customer', 'invoiceLines')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'invoice_lines' => 'required|array',
            'invoice_lines.*.item_id' => 'required|exists:products,id',
            'invoice_lines.*.quantity' => 'required|integer|min:1',
            'invoice_lines.*.line_price' => 'required|numeric|min:0',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $invoice_total = array_reduce($validated['invoice_lines'], function ($total, $line) {
            return $total + ($line['quantity'] * $line['line_price']);
        }, 0);

        if ($customer->balance < $invoice_total) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        DB::transaction(function () use ($validated, $invoice_total, $customer) {
            $invoice = Invoice::create([
                'invoice_unique_id' => now()->year . '-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT),
                'invoice_date' => $validated['invoice_date'],
                'customer_id' => $validated['customer_id'],
                'invoice_total' => $invoice_total,
            ]);

            foreach ($validated['invoice_lines'] as $line) {
                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'item_id' => $line['item_id'],
                    'quantity' => $line['quantity'],
                    'line_price' => $line['quantity'] * $line['line_price'],
                ]);

                $product = Product::find($line['item_id']);
                $product->quantity_on_hand -= $line['quantity'];
                $product->save();
            }

            $customer->balance -= $invoice_total;
            $customer->save();
        });

        return response()->json(['message' => 'Invoice created successfully'], 201);
    }

    public function show($id)
    {
        return Invoice::with('customer', 'invoiceLines')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        return response()->json(null, 204);
    }
}