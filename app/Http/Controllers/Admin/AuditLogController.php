<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->role?->canViewAuditLogs(), 403);

        $logs = AuditLog::query()
            ->with('user')
            ->when($request->string('action')->toString(), fn ($q, $action) => $q->where('action', 'like', "%{$action}%"))
            ->latest('id')
            ->paginate(40)
            ->withQueryString();

        return view('admin.audit-logs.index', compact('logs'));
    }
}
