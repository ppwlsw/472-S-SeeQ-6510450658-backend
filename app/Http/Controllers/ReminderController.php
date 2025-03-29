<?php

namespace App\Http\Controllers;

use App\Repositories\ReminderRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReminderController extends Controller
{
    public function __construct(
        private ReminderRepository $reminderRepository
    ){}


    public function index(){
        $reminders = $this->reminderRepository->getAll();
        return json_encode($reminders);
    }

    public function show($shop_id)
    {
        $shopId = (int)$shop_id;


        if (!$shopId) {
            return response()->json(['error' => 'shop_id is required'], 400);
        }

        $reminder = $this->reminderRepository->getAllRemindersByShopId($shopId);
        return response()->json($reminder);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'shop_id' => 'required | numeric',
            'title' => 'required',
            'description' => 'required',
            'due_date' => 'required | date',
        ]);

        $shopId = (int)$request->get('shop_id');
        if (!$shopId) {
            return response()->json(['error' => 'shop_id is required'], 400);

        } else {
            return $this->reminderRepository->create($validated);
        }
    }
    public function markAsDone($id){
        if (!is_numeric($id) || (int)$id <= 0) {
            return response()->json(['error' => 'Invalid reminder ID'], Response::HTTP_BAD_REQUEST);
        }

        $updated = $this->reminderRepository->markAsDone((int)$id);

        if (!$updated) {
            return response()->json(['error' => 'Reminder not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['message' => 'Reminder marked as completed'], Response::HTTP_OK);
    }




}
