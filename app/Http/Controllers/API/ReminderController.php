<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReminderResource;
use App\Models\Reminder;
use App\Repositories\ReminderRepository;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function __construct(
        private ReminderRepository $reminderRepository
    ){}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reminders = $this->reminderRepository->getAll();
        return response()->json($reminders);
        //
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
    public function store(Request $request, $shop_id)
    {
        $validated = $request->validate([
            'title' => ['required', 'min:3', 'max:255'],
            'description' => ['required'],
            'reminder_time' => ['required'],
        ]);
        $reminder = $this->reminderRepository->create([
            'title'=> $validated["title"],
            'description'=> $validated["description"],
            'shop_id' => $shop_id,
            'reminder_time' => $validated["reminder_time"],
        ]);
        return new ReminderResource($reminder);
    }

    /**
     * Display the specified resource.
     */
    public function show(Reminder $reminder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reminder $reminder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reminder $reminder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reminder $reminder)
    {
        //
    }
}
