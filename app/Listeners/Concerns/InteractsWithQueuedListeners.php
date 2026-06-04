<?php

namespace App\Listeners\Concerns;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Shared retry and failure-handling defaults for queued listeners.
 */
trait InteractsWithQueuedListeners
{
    /**
     * The number of times the queued listener may be attempted.
     */
    public int $tries = 3;

    /**
     * Seconds to wait before retrying (exponential spacing).
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * Process the job only after the database transaction commits.
     */
    public bool $afterCommit = true;

    /**
     * Called by Laravel when all retry attempts have been exhausted.
     */
    public function failed(object $event, Throwable $exception): void
    {
        Log::error('Queued listener failed permanently', [
            'listener'  => static::class,
            'event'     => $event::class,
            'message'   => $exception->getMessage(),
        ]);
    }
}
