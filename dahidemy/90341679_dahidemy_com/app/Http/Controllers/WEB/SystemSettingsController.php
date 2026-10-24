<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\DemoModeService;
use App\Services\SystemSettingService;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class SystemSettingsController extends Controller
{
    public function index(SystemSettingService $settings, DemoModeService $demoMode)
    {
        abort_unless(can_access_system('system.settings.manage'), 403);

        return view('system.settings.index', [
            'settings' => array_merge(
                $settings->allByGroup('general'),
                $settings->allByGroup('demo'),
            ),
            'demoMode' => $demoMode->enabled(),
        ]);
    }

    public function update(Request $request, SystemSettingService $settings, DemoModeService $demoMode)
    {
        abort_unless(can_access_system('system.settings.manage'), 403);
        $demoMode->check('system_settings');

        $validated = $request->validate([
            'update_server_url' => 'nullable|url|max:500',
            'update_check_enabled' => 'nullable|boolean',
            'demo_auto_reset' => 'nullable|boolean',
            'demo_reset_interval' => 'nullable|integer|min:1|max:168',
        ]);

        if (array_key_exists('update_server_url', $validated)) {
            $settings->set('update_server_url', $validated['update_server_url'], 'string', 'general');
        }

        $settings->set(
            'update_check_enabled',
            $request->boolean('update_check_enabled'),
            'boolean',
            'general'
        );

        $settings->set(
            'demo_auto_reset',
            $request->boolean('demo_auto_reset'),
            'boolean',
            'demo'
        );

        if (! empty($validated['demo_reset_interval'])) {
            $settings->set('demo_reset_interval', $validated['demo_reset_interval'], 'integer', 'demo');
        }

        Alert::toast('System settings saved.', 'success');

        return redirect()->route('system.settings.index');
    }
}
