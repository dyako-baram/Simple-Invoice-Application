<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use SoulDoit\SetEnv\Env;

class TaxRateHelper
{
    const CACHE_KEY = 'tax_rate';

    public static function getTaxRate()
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return config('app.'.self::CACHE_KEY);
        });
    }

    public static function setTaxRate($rate)
    {
        $validated = $request->validate([
            self::CACHE_KEY => 'required|numeric|min:0|max:100',
        ]);
        $envService = new Env(); 
        $envService->set(self::CACHE_KEY, $validated[self::CACHE_KEY]);
        Cache::forever(self::CACHE_KEY, $validated[self::CACHE_KEY]);
    }

    public static function resetTaxRate()
    {
        Cache::forget(self::CACHE_KEY);
        return self::getTaxRate();
    }
}
