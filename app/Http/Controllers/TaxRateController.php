<?php

namespace App\Http\Controllers;
use App\Helpers\TaxRateHelper;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    public function getTaxRate()
    {
        return response()->json(['tax_rate' => TaxRateHelper::getTaxRate()]);
    }

    public function updateTaxRate(Request $request)
    {
        $validator = $request->validate([
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        TaxRateHelper::setTaxRate($validator['tax_rate']);

        return response()->json(['tax_rate' => $validator['tax_rate']]);
    }

    public function resetTaxRate()
    {
        $taxRate = TaxRateHelper::resetTaxRate();
        return response()->json(['tax_rate' => $taxRate]);
    }
}
