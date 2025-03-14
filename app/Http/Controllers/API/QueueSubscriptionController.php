<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;

class QueueSubscriptionController extends Controller
{
    public function subscribe(Request $request, $queue_id)
    {
        $channel = "queue_updates:$queue_id";
        return response()->stream(function () use ($channel) {
            try {
                $redis = Redis::connection();
                $startTime = time();
                $timeout = 30; // 30 seconds timeout

                $redis->subscribe([$channel], function ($message) use ($startTime, $timeout) {
                    echo "data: " . $message . "\n\n";
                    ob_flush();
                    flush();

                    // Stop after timeout
                    if (time() - $startTime > $timeout) {
                        exit();
                    }
                });
            } catch (\Exception $e) {
                echo "event: error\ndata: Connection error {$e->getMessage()}\n\n";
                echo "event: error\ndata: Connection error $channel\n\n";
                ob_flush();
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }
}
