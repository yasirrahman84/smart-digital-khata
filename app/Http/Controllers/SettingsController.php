<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit(Request $request)
    {
        return view('settings.edit', ['user' => $request->user()]);
    }

    public function update(UpdateSettingsRequest $request)
    {
        $request->user()->update([
            'store_name' => $request->validated('store_name'),
            'store_phone' => $request->validated('store_phone'),
            'store_address' => $request->validated('store_address'),
            'reminder_after_days' => $request->validated('reminder_after_days'),
            'sms_enabled' => $request->boolean('sms_enabled'),
        ]);

        return redirect()
            ->route('settings.edit')
            ->with('success', 'Shop settings saved.');
    }
}
