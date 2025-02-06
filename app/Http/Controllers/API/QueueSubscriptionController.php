<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;

class QueueSubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $queueId = $request->query('queue_id');

        return response()->stream(function () use ($queueId) {
            $channel = "queue_updates:$queueId";

            Redis::subscribe([$channel], function ($message) {
                echo "data: " . $message . "\n\n";
                ob_flush();
                flush();
            });
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }
}
