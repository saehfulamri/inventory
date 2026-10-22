<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->probe(fn (): bool => DB::select('select 1') !== []),
            'cache' => $this->probe(function (): bool {
                $key = 'health-check:'.now()->timestamp;
                Cache::put($key, 'ok', 10);

                $healthy = Cache::get($key) === 'ok';
                Cache::forget($key);

                return $healthy;
            }),
            'queue' => $this->probe(function (): bool {
                $driver = config('queue.default');

                if (in_array($driver, ['sync', 'null'], true)) {
                    return true;
                }

                Queue::connection($driver)->size();

                return true;
            }),
        ];

        $healthy = ! in_array(false, $checks, true);

        return response()->json([
            'status' => $healthy ? 'ok' : 'error',
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    private function probe(callable $check): bool
    {
        try {
            return (bool) $check();
        } catch (Throwable) {
            return false;
        }
    }
}
