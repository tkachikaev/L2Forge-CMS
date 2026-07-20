<?php

namespace App\Services\Infrastructure;

use App\Models\SystemHeartbeat;
use App\Services\MailSettings;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class RuntimeDiagnostics
{
    public const SCHEDULER_HEARTBEAT = 'scheduler';

    public const QUEUE_WORKER_HEARTBEAT = 'queue-worker';

    private const HEARTBEAT_FRESH_MINUTES = 3;

    private const STALE_JOB_MINUTES = 2;

    public function __construct(
        private readonly DatabaseManager $database,
        private readonly MailSettings $mailSettings,
    ) {}

    public function recordSchedulerHeartbeat(): void
    {
        $this->touch(self::SCHEDULER_HEARTBEAT, [
            'command' => 'schedule:run',
        ]);
    }

    public function recordQueueWorker(string $connection, ?string $queue): void
    {
        if ($connection !== MailSettings::MODE_DATABASE) {
            return;
        }

        $this->touch(self::QUEUE_WORKER_HEARTBEAT, [
            'connection' => $connection,
            'queue' => $queue,
        ]);
    }

    /**
     * @return array{
     *     overall_state: string,
     *     scheduler: array{state: string, status: string, details: string, last_seen_at: Carbon|null, fresh: bool},
     *     queue: array{state: string, status: string, details: string, last_seen_at: Carbon|null, fresh: bool, requires_worker: bool, mode: string},
     *     jobs: array{available: bool, pending: int, failed: int, oldest_pending_at: Carbon|null, last_failed_at: Carbon|null},
     *     warnings: list<string>
     * }
     */
    public function overview(): array
    {
        $schedulerAt = $this->heartbeat(self::SCHEDULER_HEARTBEAT);
        $queueAt = $this->heartbeat(self::QUEUE_WORKER_HEARTBEAT);
        $schedulerFresh = $this->isFresh($schedulerAt);
        $queueFresh = $this->isFresh($queueAt);
        $jobs = $this->jobStatistics();
        $mailMode = $this->mailSettings->deliveryMode();
        $requiresWorker = $mailMode === MailSettings::MODE_DATABASE;
        $warnings = [];

        $scheduler = $this->schedulerStatus($schedulerAt, $schedulerFresh);
        $queue = $this->queueStatus(
            mode: $mailMode,
            requiresWorker: $requiresWorker,
            schedulerFresh: $schedulerFresh,
            queueAt: $queueAt,
            queueFresh: $queueFresh,
            jobs: $jobs,
        );

        if (! $schedulerFresh) {
            $warnings[] = $schedulerAt === null
                ? __('Laravel Scheduler has not recorded a successful run yet.')
                : __('Laravel Scheduler has not run for more than three minutes.');
        }

        if ($requiresWorker && $jobs['pending'] > 0 && $this->isStale($jobs['oldest_pending_at']) && ! $queueFresh) {
            $warnings[] = __('Database queue jobs have been waiting for more than two minutes without recent processing activity.');
        }

        if ($jobs['failed'] > 0) {
            $warnings[] = __('Failed queue jobs: :count.', [
                'count' => $jobs['failed'],
            ]);
        }

        return [
            'overall_state' => $this->worstState([
                $scheduler['state'],
                $queue['state'],
                $jobs['failed'] > 0 ? 'warning' : 'success',
            ]),
            'scheduler' => $scheduler,
            'queue' => $queue,
            'jobs' => $jobs,
            'warnings' => $warnings,
        ];
    }

    /**
     * @return array{state: string, status: string, details: string, last_seen_at: Carbon|null, fresh: bool}
     */
    private function schedulerStatus(?Carbon $lastSeenAt, bool $fresh): array
    {
        if ($fresh && $lastSeenAt !== null) {
            return [
                'state' => 'success',
                'status' => __('Working'),
                'details' => __('Last successful run: :time', ['time' => $lastSeenAt->diffForHumans()]),
                'last_seen_at' => $lastSeenAt,
                'fresh' => true,
            ];
        }

        return [
            'state' => $lastSeenAt === null ? 'warning' : 'danger',
            'status' => $lastSeenAt === null ? __('Not recorded') : __('Overdue'),
            'details' => $lastSeenAt === null
                ? __('Run php artisan schedule:run every minute on the server.')
                : __('Last successful run: :time', ['time' => $lastSeenAt->diffForHumans()]),
            'last_seen_at' => $lastSeenAt,
            'fresh' => false,
        ];
    }

    /**
     * @param  array{available: bool, pending: int, failed: int, oldest_pending_at: Carbon|null, last_failed_at: Carbon|null}  $jobs
     * @return array{state: string, status: string, details: string, last_seen_at: Carbon|null, fresh: bool, requires_worker: bool, mode: string}
     */
    private function queueStatus(
        string $mode,
        bool $requiresWorker,
        bool $schedulerFresh,
        ?Carbon $queueAt,
        bool $queueFresh,
        array $jobs,
    ): array {
        if (! $jobs['available']) {
            return [
                'state' => 'danger',
                'status' => __('Unavailable'),
                'details' => __('Queue tables are missing or cannot be read.'),
                'last_seen_at' => $queueAt,
                'fresh' => $queueFresh,
                'requires_worker' => $requiresWorker,
                'mode' => $mode,
            ];
        }

        if (! $requiresWorker) {
            return [
                'state' => 'success',
                'status' => $mode === MailSettings::MODE_SYNC ? __('Synchronous mode') : __('Background mode'),
                'details' => __('A permanent database queue worker is not required.'),
                'last_seen_at' => $queueAt,
                'fresh' => $queueFresh,
                'requires_worker' => false,
                'mode' => $mode,
            ];
        }

        $stalePendingJob = $jobs['pending'] > 0 && $this->isStale($jobs['oldest_pending_at']);

        if ($stalePendingJob && ! $queueFresh) {
            return [
                'state' => 'danger',
                'status' => __('Jobs are not being processed'),
                'details' => $schedulerFresh
                    ? __('Scheduler is running, but the database queue has no recent processing activity.')
                    : __('Scheduler and the database queue require attention.'),
                'last_seen_at' => $queueAt,
                'fresh' => false,
                'requires_worker' => true,
                'mode' => $mode,
            ];
        }

        if (! $schedulerFresh) {
            return [
                'state' => 'warning',
                'status' => __('Waiting for Scheduler'),
                'details' => __('Database queue processing depends on Scheduler or a permanent queue worker.'),
                'last_seen_at' => $queueAt,
                'fresh' => $queueFresh,
                'requires_worker' => true,
                'mode' => $mode,
            ];
        }

        return [
            'state' => 'success',
            'status' => __('Ready'),
            'details' => $queueAt !== null
                ? __('Last queue activity: :time', ['time' => $queueAt->diffForHumans()])
                : __('Scheduler is running. Queue activity will appear after the first database job.'),
            'last_seen_at' => $queueAt,
            'fresh' => $queueFresh,
            'requires_worker' => true,
            'mode' => $mode,
        ];
    }

    /**
     * @return array{available: bool, pending: int, failed: int, oldest_pending_at: Carbon|null, last_failed_at: Carbon|null}
     */
    private function jobStatistics(): array
    {
        $empty = [
            'available' => false,
            'pending' => 0,
            'failed' => 0,
            'oldest_pending_at' => null,
            'last_failed_at' => null,
        ];

        try {
            $defaultConnection = (string) config('database.default');
            $queueConnectionName = (string) config('queue.connections.database.connection', '');
            $failedConnectionName = (string) config('queue.failed.database', '');
            $jobsTable = (string) config('queue.connections.database.table', 'jobs');
            $failedJobsTable = (string) config('queue.failed.table', 'failed_jobs');
            $queueConnection = $this->database->connection($queueConnectionName !== '' ? $queueConnectionName : $defaultConnection);
            $failedConnection = $this->database->connection($failedConnectionName !== '' ? $failedConnectionName : $defaultConnection);

            if (! $queueConnection->getSchemaBuilder()->hasTable($jobsTable)
                || ! $failedConnection->getSchemaBuilder()->hasTable($failedJobsTable)) {
                return $empty;
            }

            $oldestCreatedAt = $queueConnection->table($jobsTable)->min('created_at');
            $lastFailedAt = $failedConnection->table($failedJobsTable)->max('failed_at');

            return [
                'available' => true,
                'pending' => $queueConnection->table($jobsTable)->count(),
                'failed' => $failedConnection->table($failedJobsTable)->count(),
                'oldest_pending_at' => is_numeric($oldestCreatedAt)
                    ? Carbon::createFromTimestamp((int) $oldestCreatedAt)
                    : null,
                'last_failed_at' => is_string($lastFailedAt) && $lastFailedAt !== ''
                    ? Carbon::parse($lastFailedAt)
                    : null,
            ];
        } catch (Throwable) {
            return $empty;
        }
    }

    /** @param array<string, mixed> $metadata */
    private function touch(string $key, array $metadata): void
    {
        try {
            if (! Schema::hasTable('system_heartbeats')) {
                return;
            }

            SystemHeartbeat::query()->updateOrCreate(
                ['key' => $key],
                [
                    'last_seen_at' => now(),
                    'metadata' => $metadata,
                ],
            );
        } catch (Throwable) {
            // Monitoring must never break Scheduler or queue processing.
        }
    }

    private function heartbeat(string $key): ?Carbon
    {
        try {
            if (! Schema::hasTable('system_heartbeats')) {
                return null;
            }

            $value = SystemHeartbeat::query()->whereKey($key)->value('last_seen_at');

            return $value instanceof Carbon
                ? $value
                : (is_string($value) ? Carbon::parse($value) : null);
        } catch (Throwable) {
            return null;
        }
    }

    private function isFresh(?Carbon $timestamp): bool
    {
        return $timestamp !== null && $timestamp->gte(now()->subMinutes(self::HEARTBEAT_FRESH_MINUTES));
    }

    private function isStale(?Carbon $timestamp): bool
    {
        return $timestamp !== null && $timestamp->lt(now()->subMinutes(self::STALE_JOB_MINUTES));
    }

    /** @param list<string> $states */
    private function worstState(array $states): string
    {
        foreach (['danger', 'warning', 'neutral', 'success'] as $state) {
            if (in_array($state, $states, true)) {
                return $state;
            }
        }

        return 'neutral';
    }
}
