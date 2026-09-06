<?php

namespace App\Services;

use App\Exceptions\ApprovalException;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalWorkflowService
{
    /**
     * Cek apakah modul/transaksi memerlukan approval.
     */
    public function isApprovalRequired(string $entityType): bool
    {
        return config("erp.approval.{$entityType}.required", false);
    }

    /**
     * Cek apakah user yang login berhak melakukan approval pada transaksi ini.
     */
    public function canApprove(User $user, Model $transaction, string $entityType, int $level = 1): bool
    {
        if (!$this->isApprovalRequired($entityType)) {
            return true;
        }

        $permissionNeeded = config("erp.approval.{$entityType}.permissions.{$level}", 'inventory.approve');

        // Jika user mempunyai permission khusus via Spatie / Check
        if (method_exists($user, 'hasPermissionTo')) {
            return $user->hasPermissionTo($permissionNeeded) || $user->hasRole('superadmin');
        }

        // Fallback untuk role existing
        if ($user->roles()->exists()) {
            $roleName = strtolower($user->getRoleName());
            return in_array($roleName, ['superadmin', 'kepala gudang', 'warehouse manager']);
        }

        return false;
    }

    /**
     * Proses approval transaksi.
     */
    public function approve(Model $transaction, User $approver, ?string $notes = null, string $entityType = 'barang_masuk'): Model
    {
        if (!$this->canApprove($approver, $transaction, $entityType)) {
            throw new ApprovalException("User {$approver->name} tidak memiliki hak akses untuk menyetujui transaksi ini.");
        }

        DB::transaction(function () use ($transaction, $approver, $notes) {
            $transaction->status = 'APPROVED';
            $transaction->approved_by = $approver->id;
            $transaction->approved_at = now();
            if ($notes) {
                $transaction->approval_notes = $notes;
            }
            $transaction->save();

            // Catat di activity log
            activity()
                ->performedOn($transaction)
                ->causedBy($approver)
                ->withProperties(['notes' => $notes, 'status' => 'APPROVED'])
                ->log("Transaksi {$transaction->kode_transaksi} telah disetujui oleh {$approver->name}");
        });

        return $transaction;
    }

    /**
     * Proses penolakan (rejection) transaksi.
     */
    public function reject(Model $transaction, User $approver, string $reason, string $entityType = 'barang_masuk'): Model
    {
        if (empty(trim($reason))) {
            throw new ApprovalException("Alasan penolakan (rejection) wajib diisi!");
        }

        if (!$this->canApprove($approver, $transaction, $entityType)) {
            throw new ApprovalException("User {$approver->name} tidak memiliki hak akses untuk menolak transaksi ini.");
        }

        DB::transaction(function () use ($transaction, $approver, $reason) {
            $transaction->status = 'REJECTED';
            $transaction->approved_by = $approver->id;
            $transaction->approved_at = now();
            $transaction->approval_notes = $reason;
            $transaction->save();

            activity()
                ->performedOn($transaction)
                ->causedBy($approver)
                ->withProperties(['reason' => $reason, 'status' => 'REJECTED'])
                ->log("Transaksi {$transaction->kode_transaksi} ditolak oleh {$approver->name}. Alasan: {$reason}");
        });

        return $transaction;
    }
}
