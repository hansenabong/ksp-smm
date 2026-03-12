<?php

namespace App\Http\Controllers\Admin\Audit;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    private const ACTIONS = [
        'void'    => 'VOID',
        'approve' => 'Approve',
        'reject'  => 'Tolak',
        'login'   => 'Login',
        'logout'  => 'Logout',
        'create'  => 'Buat',
        'update'  => 'Ubah',
        'delete'  => 'Hapus',
    ];

    public function index(Request $request)
    {
        $query = AuditLog::with('user')
            ->orderByDesc('created_at');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->paginate(25)->withQueryString();

        $users       = User::orderBy('name')->get(['id', 'name', 'email']);
        $actions     = self::ACTIONS;
        $subjectTypes = AuditLog::distinct()->pluck('subject_type')->filter()->sort()->values();

        return view('admin.audit.log.index', compact('logs', 'users', 'actions', 'subjectTypes'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = AuditLog::with('user')->orderByDesc('created_at');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->input('date_to', now()->toDateString());
        $filename = "audit_log_{$dateFrom}_{$dateTo}.csv";

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'created_at', 'user', 'action', 'subject_type', 'subject_id',
                'description', 'ip_address', 'metadata',
            ], ';');

            foreach ($query->cursor() as $log) {
                fputcsv($out, [
                    $log->created_at,
                    $log->user?->email ?? '(system)',
                    $log->action,
                    $log->subject_type ?? '',
                    $log->subject_id ?? '',
                    $log->description ?? '',
                    $log->ip_address ?? '',
                    $log->metadata ? json_encode($log->metadata, JSON_UNESCAPED_UNICODE) : '',
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
