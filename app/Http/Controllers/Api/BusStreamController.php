<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusSchedule;
use App\Services\BusLiveStream;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BusStreamController extends Controller
{
    /** Server-Sent Events stream — pushes GPS updates as the driver pings. */
    public function stream(int $id): StreamedResponse
    {
        abort_unless(BusSchedule::whereKey($id)->exists(), 404);

        return response()->stream(function () use ($id) {
            if (function_exists('set_time_limit')) {
                @set_time_limit(0);
            }

            $lastSeq = (int) (BusLiveStream::read($id)['_seq'] ?? 0);
            $started = time();
            $lastPing = 0;

            while (!connection_aborted() && (time() - $started) < 180) {
                $payload = BusLiveStream::read($id);
                $seq = (int) ($payload['_seq'] ?? 0);

                if ($payload && $seq > $lastSeq) {
                    $lastSeq = $seq;
                    echo "event: location\n";
                    echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";
                    self::flush();
                } elseif (time() - $lastPing >= 15) {
                    echo ": keepalive\n\n";
                    self::flush();
                    $lastPing = time();
                }

                usleep(250000);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    protected static function flush(): void
    {
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
}
