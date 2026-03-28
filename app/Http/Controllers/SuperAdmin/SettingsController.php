<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function settings()
    {
        $settings = Setting::getGrouped();
        $groups = ['general', 'email', 'security', 'features', 'notifications', 'system'];
        $darkMode = Setting::get('dark_mode', false);

        return view('super-admin.settings', compact('settings', 'groups', 'darkMode'));
    }

    public function checkDarkMode()
    {
        $darkMode = Setting::get('dark_mode', false);

        return response()->json(['darkMode' => $darkMode]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate(['settings' => 'required|array']);

        foreach ($request->settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();

            if ($setting) {
                if ($setting->type === 'boolean') {
                    $value = isset($value) && $value !== '0' && $value !== 'false';
                } elseif ($setting->type === 'integer') {
                    $value = (int) $value;
                } elseif ($setting->type === 'json' && is_array($value)) {
                    $value = json_encode($value);
                }

                $setting->update(['value' => $value]);
            }
        }

        Setting::clearCache();

        return back()->with('success', 'Settings updated successfully.');
    }

    public function updateSettingsGroup(Request $request, $group)
    {
        $validGroups = ['general', 'email', 'security', 'features', 'notifications', 'system'];

        if (! in_array($group, $validGroups)) {
            return back()->with('error', 'Invalid settings group.');
        }

        $settings = Setting::getByGroup($group);
        $rules = [];

        foreach ($settings as $setting) {
            $rules["settings.{$setting->key}"] = $this->getValidationRule($setting);
        }

        $request->validate($rules);

        foreach ($request->settings as $key => $value) {
            $setting = Setting::where('key', $key)->where('group', $group)->first();

            if ($setting) {
                if ($setting->type === 'boolean') {
                    $value = isset($value) && $value !== '0' && $value !== 'false';
                } elseif ($setting->type === 'integer') {
                    $value = (int) $value;
                } elseif ($setting->type === 'json' && is_array($value)) {
                    $value = json_encode($value);
                }

                $setting->update(['value' => $value]);
            }
        }

        Setting::clearCache();

        return back()->with('success', ucfirst($group).' settings updated successfully.');
    }

    protected function getValidationRule($setting)
    {
        return match ($setting->type) {
            'integer' => 'nullable|integer',
            'boolean' => 'nullable|boolean',
            'email' => 'nullable|email',
            'url' => 'nullable|url',
            default => 'nullable|string|max:1000',
        };
    }
}
