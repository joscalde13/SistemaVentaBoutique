<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BusinessSettingController extends Controller
{
    public function edit(): View
    {
        $settings = BusinessSetting::query()->firstOrCreate(['id' => 1]);

        return view('business-settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:180'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'currency_code' => ['required', 'string', 'size:3', 'alpha'],
            'currency_symbol' => ['required', 'string', 'max:5'],
            'logo' => ['nullable', 'image', 'max:4096'],
        ]);

        $settings = BusinessSetting::query()->firstOrCreate(['id' => 1]);
        $previousLogo = $settings->logo_path;
        $newLogo = $request->file('logo')?->store('business', 'public');

        $settings->update([
            ...collect($data)->except('logo')->all(),
            'logo_path' => $newLogo ?? $previousLogo,
        ]);

        if ($newLogo && $previousLogo) {
            Storage::disk('public')->delete($previousLogo);
        }

        return to_route('business-settings.edit')->with('success', 'La configuración del negocio se guardó correctamente.');
    }
}
