<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Api\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BackupController extends Controller
{
    public function __construct(protected BackupService $backups) {}

    public function index(): View
    {
        $response = $this->backups->list();
        $data = (array) $response->data([]);

        return view('admin.backups.index', [
            'reachable' => $response->successful(),
            'remoteConfigured' => (bool) ($data['remote_configured'] ?? false),
            'backups' => (array) ($data['backups'] ?? []),
        ]);
    }

    public function run(): RedirectResponse
    {
        $response = $this->backups->run();

        return back()->with(
            $response->successful() ? 'success' : 'error',
            $response->successful() ? ($response->message ?: 'Backup completed.') : ($response->message ?: 'Backup failed.')
        );
    }
}
