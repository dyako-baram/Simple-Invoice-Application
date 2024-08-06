<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        
        return response()->json($settings ?: new Setting(), 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tax_rate' => 'required|numeric|between:0,100',
        ]);

        $settings = Setting::updateOrCreate(
            $validated
        );

        return response()->json($settings, 201);
    }

    public function show($id)
    {
        
        $setting = Setting::findOrFail($id);
        return response()->json($setting);
    }

    public function update(Request $request, $id)
    {
        
        $setting = Setting::findOrFail($id);

        $validated = $request->validate([
            'tax_rate' => 'required|numeric|between:0,100',
        ]);

        $setting->update($validated);

        return response()->json($setting);
    }

    public function destroy($id)
    {
        $setting = Setting::findOrFail($id);
        $setting->delete();

        return response()->json(null, 204);
    }
}
