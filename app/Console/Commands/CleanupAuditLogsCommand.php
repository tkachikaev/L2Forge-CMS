<?php

namespace App\Console\Commands;

use App\Models\AdminLoginLog;
use App\Models\AuditLog;
use App\Services\AuditLogger;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;

class CleanupAuditLogsCommand extends Command
{
    private const BATCH_SIZE = 1000;

    protected $signature = 'l2forge:logs-clean
        {--days= : Delete audit entries older than the specified number of days}
        {--admin-login-days= : Delete administrator login entries older than the specified number of days}
        {--dry-run : Show the number of entries without deleting them}';

    protected $description = 'Remove expired audit and administrator login log entries';

    public function handle(AuditLogger $auditLogger): int
    {
        $auditDays = $this->option('days') !== null
            ? (int) $this->option('days')
            : (int) config('cms.audit.retention_days', 90);
        $adminLoginDays = $this->option('admin-login-days') !== null
            ? (int) $this->option('admin-login-days')
            : (int) config('cms.admin.login_log_retention_days', 30);

        if (! $this->validRetentionDays($auditDays) || ! $this->validRetentionDays($adminLoginDays)) {
            $this->error(__('The number of days must be between 1 and 3650.'));

            return self::FAILURE;
        }

        $auditThreshold = now()->subDays($auditDays);
        $adminLoginThreshold = now()->subDays($adminLoginDays);
        $auditCount = AuditLog::query()->where('created_at', '<', $auditThreshold)->count();
        $adminLoginCount = AdminLoginLog::query()->where('created_at', '<', $adminLoginThreshold)->count();

        if ($this->option('dry-run')) {
            $this->info(__('Audit log').': '.__('Entries to delete: :count.', ['count' => $auditCount]));
            $this->line(__('Audit log').': '.__('Retention threshold: :date.', [
                'date' => $auditThreshold->format('d.m.Y H:i:s'),
            ]));
            $this->info(__('Administrator login log').': '.__('Entries to delete: :count.', [
                'count' => $adminLoginCount,
            ]));
            $this->line(__('Administrator login log').': '.__('Retention threshold: :date.', [
                'date' => $adminLoginThreshold->format('d.m.Y H:i:s'),
            ]));

            return self::SUCCESS;
        }

        $deletedAudit = $this->deleteAuditLogs($auditThreshold);
        $deletedAdminLogin = $this->deleteAdminLoginLogs($adminLoginThreshold);

        if ($deletedAudit > 0 || $deletedAdminLogin > 0) {
            $auditLogger->system('system', 'audit.cleaned', details: [
                'audit_retention_days' => $auditDays,
                'audit_deleted_count' => $deletedAudit,
                'admin_login_retention_days' => $adminLoginDays,
                'admin_login_deleted_count' => $deletedAdminLogin,
            ]);
        }

        $this->info(__('Audit log').': '.__('Deleted entries: :count.', ['count' => $deletedAudit]));
        $this->info(__('Administrator login log').': '.__('Deleted entries: :count.', [
            'count' => $deletedAdminLogin,
        ]));

        return self::SUCCESS;
    }

    private function validRetentionDays(int $days): bool
    {
        return $days >= 1 && $days <= 3650;
    }

    private function deleteAuditLogs(CarbonInterface $threshold): int
    {
        $deleted = 0;

        do {
            $ids = AuditLog::query()
                ->where('created_at', '<', $threshold)
                ->orderBy('id')
                ->limit(self::BATCH_SIZE)
                ->pluck('id')
                ->all();

            if ($ids === []) {
                break;
            }

            $deleted += AuditLog::query()->whereIn('id', $ids)->delete();
        } while (count($ids) === self::BATCH_SIZE);

        return $deleted;
    }

    private function deleteAdminLoginLogs(CarbonInterface $threshold): int
    {
        $deleted = 0;

        do {
            $ids = AdminLoginLog::query()
                ->where('created_at', '<', $threshold)
                ->orderBy('id')
                ->limit(self::BATCH_SIZE)
                ->pluck('id')
                ->all();

            if ($ids === []) {
                break;
            }

            $deleted += AdminLoginLog::query()->whereIn('id', $ids)->delete();
        } while (count($ids) === self::BATCH_SIZE);

        return $deleted;
    }
}
