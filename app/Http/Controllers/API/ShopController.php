<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateShopRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Http\Resources\ShopResource;
use App\Http\Resources\IdResource;
use App\Mail\VerificationEmail;
use App\Models\Shop;
use App\Repositories\ShopRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(
        private ShopRepository $shopRepository
    ) {}

    public function index()
    {
        Gate::authorize('viewAny', Shop::class);
        $shops = $this->shopRepository->getAll();
        return ShopResource::collection($shops);
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
    public function store(CreateShopRequest $request)
    {
//        Gate::authorize('create', Shop::class);
        $validated = $request->validated();

        $shop = $this->shopRepository->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'verification_token' => Str::random(40),
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'description' => $validated['description'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_open' => false,
        ]);

        Mail::to($shop->email)->send(new VerificationEmail($shop));

        return IdResource::make($shop);
    }

    public function verify($token)
    {
        $shop = Shop::where('verification_token', $token)->first();

        if (!$shop) {
            return response()->json(['message' => 'Token ไม่ถูกต้อง'], 404);
        }

        $shop->verification_token = null;
        $shop->email_verified_at = now();
        $shop->save();

        return view('emails.verifysuccess', ['path_link' => url('http://localhost:5173/dashboard/shop')]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Shop $shop)
    {
        Gate::authorize('view', Shop::class);
        return ShopResource::make($shop);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shop $shop)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShopRequest $request, Shop $shop)
    {
        Gate::authorize('update', $shop);
        $shop->update([
            'name' => $request->get('name'),
            'address' => $request->get('address'),
            'phone' => $request->get('shop_phone'),
            'description' => $request->get('description'),
            'image_uri' => $request->get('image_uri'),
            'is_open' => $request->get('is_open'),
            'approve_status' => $request->get('approve_status'),
            'latitude' => $request->get('latitude'),
            'longitude' => $request->get('longitude'),
            'user_id' => auth()->id(),
        ]);
        return IdResource::make($shop);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop)
    {
        Gate::authorize('delete', $shop);
        $shop->delete();
    }

}
