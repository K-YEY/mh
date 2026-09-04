<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $logs = DB::table('activity_log')
            ->leftJoin('users', 'activity_log.causer_id', '=', 'users.id')
            ->select('activity_log.*', 'users.name as causer_name', 'users.email as causer_email')
            ->when($request->search, function ($query, $search) {
                $query->where('description', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%");
            })
            ->latest('activity_log.created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['search']),
        ]);
    }
}
