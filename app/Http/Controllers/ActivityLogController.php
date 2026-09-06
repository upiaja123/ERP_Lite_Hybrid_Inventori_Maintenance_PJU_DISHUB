<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of audit activity logs.
     */
    public function index()
    {
        // Enforce role check: Only Superadmin and Kepala Gudang can view Audit Logs
        if (!auth()->user()->hasRole(['superadmin', 'kepala gudang'])) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki hak akses untuk melihat Audit Log!');
        }

        $log = Activity::with('causer')->latest()->get();

        return view('aktivitas-user.index', [
            'logs' => $log
        ]);
    }

    /**
     * Security Rule: Audit log MUST NOT be deleted by ordinary users or admins.
     */
    public function destroy($id)
    {
        abort(403, 'Keamanan Sistem: Audit Log bersifat Append-Only dan TIDAK BOLEH dihapus!');
    }
}
