<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;

class QueueSubscriptionController extends Controller
{
    public function subscribe(Request $request, $queue_id)
    {

        return response()->stream(function () use ($queue_id) {
            $channel = "queue_updates:$queue_id";

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
