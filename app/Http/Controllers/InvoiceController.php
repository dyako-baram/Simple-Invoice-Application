<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Customer;
use App\Models\Product;
use App\Helpers\TaxRateHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function index()
    {
        try {
            $invoices = Invoice::where('user_id', Auth::id())
                            ->with('customer', 'invoiceLines')
                            ->get();

            return response()->json($invoices);
        } catch (\Exception $e) {
            Log::error('Error fetching invoices: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch invoices'], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_lines' => 'required|array',
            'invoice_lines.*.item_id' => 'required|exists:products,id',
            'invoice_lines.*.quantity' => 'required|integer|min:1',
            'invoice_lines.*.line_price' => 'required|numeric|min:0',
        ]);

        $taxRate = TaxRateHelper::getTaxRate();
        $taxThreshold = 50;

        try {
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

            DB::transaction(function () use ($validated, $invoice_total, $customer, $taxRate) {
                $lastInvoice = Invoice::where('user_id', Auth::id())
                            ->orderBy('id', 'desc')
                            ->first();

                if ($lastInvoice) {
                    $lastInvoiceNumber = (int) substr($lastInvoice->invoice_unique_id, -4);
                    $nextInvoiceNumber = str_pad($lastInvoiceNumber + 1, 4, '0', STR_PAD_LEFT);
                } else {
                    $nextInvoiceNumber = '0001';
                }

                $invoice_unique_id = now()->year . '-' . $nextInvoiceNumber;

                $invoice = Invoice::create([
                    'invoice_unique_id' => $invoice_unique_id,
                    'invoice_date' => now(),
                    'customer_id' => $validated['customer_id'],
                    'tax_rate' => (float)$taxRate,
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
        } catch (\Exception $e) {
            Log::error('Error creating invoice: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create invoice'], 500);
        }
    }

    public function show($id)
    {
        try {
            $invoice = Invoice::where('user_id', Auth::id())->with('customer', 'invoiceLines')->findOrFail($id);
            return response()->json($invoice);
        } catch (\Exception $e) {
            Log::error('Error fetching invoice: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch invoice'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $invoice = Invoice::where('user_id', Auth::id())->findOrFail($id);

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

            $new_invoice_total = array_reduce($validated['invoice_lines'], function ($total, $line) {
                return $total + ($line['quantity'] * $line['line_price']);
            }, 0);

            if ($new_invoice_total >= $taxThreshold) {
                $taxAmount = $new_invoice_total * ($taxRate / 100);
                $new_invoice_total += $taxAmount;
            }

            $difference = $new_invoice_total - $invoice->invoice_total;

            if ($customer->balance < $difference) {
                return response()->json(['message' => 'Insufficient balance'], 400);
            }

            DB::transaction(function () use ($validated, $invoice, $new_invoice_total, $customer, $taxRate, $difference) {
                foreach ($invoice->invoiceLines as $line) {
                    $product = Product::findOrFail($line->item_id);
                    $product->quantity_on_hand += $line->quantity;
                    $product->save();
                }
                $customer->balance += $invoice->invoice_total;

                $invoice->update([
                    'customer_id' => $validated['customer_id'],
                    'invoice_date' => $validated['invoice_date'],
                    'tax_rate' => $taxRate,
                    'invoice_total' => $new_invoice_total,
                ]);

                $invoice->invoiceLines()->delete();

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

                $customer->balance -= $new_invoice_total;
                $customer->save();
            });

            return response()->json(['message' => 'Invoice updated successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error updating invoice: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update invoice'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $invoice = Invoice::where('user_id', Auth::id())->findOrFail($id);
                $invoice->invoiceLines()->delete();
                $invoice->delete();
            });

            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting invoice: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete invoice'], 500);
        }
    }
}
