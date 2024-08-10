<?php

namespace App\Http\Controllers;

use App\Helpers\TaxRateHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaxRateController extends Controller
{
    public function getTaxRate()
    {
        try {
            $taxRate = TaxRateHelper::getTaxRate();
            return response()->json(['tax_rate' => $taxRate]);
        } catch (\Exception $e) {
            Log::error('Error fetching tax rate: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch tax rate'], 500);
        }
    }

    public function updateTaxRate(Request $request)
    {
        $validated = $request->validate([
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        try {
            TaxRateHelper::setTaxRate($validated['tax_rate']);
            return response()->json(['tax_rate' => $validated['tax_rate']]);
        } catch (\Exception $e) {
            Log::error('Error updating tax rate: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update tax rate'], 500);
        }
    }

    public function resetTaxRate()
    {
        try {
            $taxRate = TaxRateHelper::resetTaxRate();
            return response()->json(['tax_rate' => $taxRate]);
        } catch (\Exception $e) {
            Log::error('Error resetting tax rate: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to reset tax rate'], 500);
        }
    }
}
