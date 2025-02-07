<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\QueueCollection;
use App\Http\Resources\QueueResource;
use App\Models\Queue;
use App\Repositories\QueueRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class QueueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(
        private QueueRepository $queueRepository
    ) {}

    public function index()
    {
        $shops = $this->queueRepository->getAll();
        return new QueueCollection($shops);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'is_available' => 'required',
            'shop_id' => 'required'
        ]);

        $queue = $this->queueRepository->create([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'is_available' => $request->get('is_available'),
            'shop_id' => $request->get('shop_id')
        ]);
        return new QueueResource($queue);
    }

    /**
     * Display the specified resource.
     */
    public function show(Queue $queue)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Queue $queue)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Queue $queue)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Queue $queue)
    {
        //
    }

    public function joinQueue(Request $request, $queue_id)
    {
        $user_id = auth()->id();
        $queue = $this->queueRepository->getById($queue_id);
        if (!$queue){
            return response()->json(["message" => "Queue not found"], 404);
        }

        $queueKey = "queue:$queue->id";

        if (Redis::lpos($queueKey, $user_id) !== false){
            return response()->json(["message" => "Queue already joined"], 400);
        }

        Redis::lpush($queueKey, $user_id);

        return response()->json(["message" => "User is in queue now",], 200);
    }

    public function status(Request $request, $queue_id)
    {
        $user_id = auth()->id();
        $queue = $this->queueRepository->getById($queue_id);
        if (!$queue){
            return response()->json(["message" => "Queue not found"], 404);
        }

        $queueKey = "queue:$queue->id";

        // Get the list of users in the queue
        $queueList = Redis::lrange($queueKey, 0, -1);

        // Reverse the list so that the front of the queue is at index 0
        $queueList = array_reverse($queueList);

        // Find the position of the user in the queue list
        $position = array_search($user_id, $queueList);

        if ($position === false) {
            return response()->json(["message" => "User is not in queue"], 303);
        }

        // Human-readable position (1-based index)
        $humanReadablePosition = $position + 1;

        return response()->json([
            "message" => "User's queue position",
            "position" => $humanReadablePosition,
            "user_id" => $user_id,
        ], 200);

    }

    public function cancel(Request $request, $queue_id)
    {
        $user_id = auth()->id();
        $queue = $this->queueRepository->getById($queue_id);
        if (!$queue){
            return response()->json(["message" => "Queue not found"], 404);
        }

        $queueKey = "queue:$queue_id";

        // Check if user is in queue
        if (Redis::lpos($queueKey, $user_id) === false) {
            return response()->json(["message" => "User is not in queue"], 404);
        }

        Redis::lrem($queueKey, 1, $user_id);
        // 📢 Notify via Redis Pub/Sub
        Redis::publish("queue_updates:$queue_id", json_encode([
            "event" => "cancel",
            "user_id" => $user_id
        ]));

        return response()->json([
            "message" => "Remove from queue now",
            "removeStatus" => true
        ], 200);

    }

    public function next(Request $request, $queue_id)
    {
        $queue = $this->queueRepository->getById($queue_id);
        if (!$queue){
            return response()->json(["message" => "Queue not found"], 404);
        }

        $queueKey = "queue:$queue_id";

        $nextUserId = Redis::rpop($queueKey);

        if(!$nextUserId){
            return response()->json(["message" => "Queue is empty"], 200);
        }
        // 📢 Notify via Redis Pub/Sub
        Redis::publish("queue_updates:$queue_id", json_encode([
            "event" => "next",
            "user_id" => $nextUserId
        ]));

        return response()->json([
            "message" => "Next user called",
            "next_user_id" => $nextUserId
        ], 200);
    }

    public function getAllQueues(Request $request, $queue_id)
    {
        $queue = Redis::lrange("queue:$queue_id", 0, -1);

        // Check if the queue is empty
        if (empty($queue)) {
            return response()->json([
                "message" => "No users in the queue",
                "Result" => $queue
            ], 200);
        }

        return response()->json([
            "Result" => $queue, ], 200);
    }
}
