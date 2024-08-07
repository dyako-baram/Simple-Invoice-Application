<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Customer;
use App\Models\Product;
use App\Helpers\TaxRateHelper;
use Illuminate\Support\Facades\Auth;
use DB;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::where('user_id', Auth::id())
                        ->with('customer', 'invoiceLines')
                        ->get();

        return response()->json($invoices);
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

        $taxRate = TaxRateHelper::getTaxRate();
        $taxThreshold = 50;

        $customer = Customer::where('user_id', Auth::id())->findOrFail($validated['customer_id']);
        $invoice_total = array_reduce($validated['invoice_lines'], function ($total, $line) {
            return $total + ($line['quantity'] * $line['line_price']);
        }, 0);

        if ($invoice_total >= $taxThreshold) {
            $taxAmount = $invoice_total * ($taxRate / 100);
            $invoice_total += $taxAmount;
        }

        if ($customer->balance < $invoice_total) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        DB::transaction(function () use ($validated, $invoice_total, $customer) {
            $invoice = Invoice::create([
                'invoice_unique_id' => now()->year . '-' . str_pad(Invoice::where('user_id', Auth::id())->count() + 1, 4, '0', STR_PAD_LEFT),
                'invoice_date' => $validated['invoice_date'],
                'customer_id' => $validated['customer_id'],
                'tax_rate' => TaxRateHelper::getTaxRate(),
                'invoice_total' => $invoice_total,
                'user_id' => Auth::id(), 
            ]);

            foreach ($validated['invoice_lines'] as $line) {
                 InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'user_id' => Auth::id(),
                    'item_id' => $line['item_id'],
                    'quantity' => $line['quantity'],
                    'line_price' => $line['quantity'] * $line['line_price'],
                ]);

                $product = Product::findOrFail($line['item_id']);
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
        $invoice = Invoice::where('user_id', Auth::id())->with('customer', 'invoiceLines')->findOrFail($id);
        return response()->json($invoice);
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::where('user_id', Auth::id())->findOrFail($id);
    }

    public function destroy($id)
    {
        $invoice = Invoice::where('user_id', Auth::id())->findOrFail($id);
        $invoice->delete();

        return response()->json(null, 204);
    }
}