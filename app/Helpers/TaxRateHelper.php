<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use SoulDoit\SetEnv\Env;

class TaxRateHelper
{
    const CACHE_KEY = 'TAX_RATE';

    public static function getTaxRate()
    {
        return (float)Cache::rememberForever(self::CACHE_KEY, function () {
            return config('app.'.self::CACHE_KEY);
        });
    }

    public static function setTaxRate($rate)
    {
        
        $envService = new Env(); 
        $envService->set(self::CACHE_KEY, (float)$rate);
        Cache::forever(self::CACHE_KEY, $rate);
    }

    public static function resetTaxRate()
    {
        Cache::forget(self::CACHE_KEY);
        return self::getTaxRate();
    }
}
