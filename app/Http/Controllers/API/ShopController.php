<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateShopRequest;
use App\Http\Requests\UpdateImageRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Http\Resources\ShopResource;
use App\Http\Resources\IdResource;
use App\Mail\VerificationEmail;
use App\Models\Shop;
use App\Repositories\ShopRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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

    public function filterShop(Request $request)
    {

        $shops = $this->shopRepository->filter($request->all());

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
        Gate::authorize('create', Shop::class);

        $shop = $this->shopRepository->create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'password' => bcrypt($request->password),
            'phone' => $request->shop_phone,
            'address' => $request->address,
            'description' => $request->description,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'verification_token' => Str::random(40),
        ]);

        if ($request->hasFile('image')) {
            $file = $request->image;
            $filename = now()->format('Y-m-d_H:i:s.u') . '.png';
            $path = 'shop_images/'. $shop->id .'/'. $filename;
            Storage::disk('s3')->put($path, file_get_contents($file), 'private');
            $uri = str_replace('/', '+', $path);
            $shop->update([
                'image_url' => env("APP_URL") . 'api/images/' . $uri
            ]);
        }

        Mail::to($shop->email)->send(new VerificationEmail($shop));

        return IdResource::make($shop);
    }

    public function verify($token)
    {
        $shop = Shop::where('verification_token', $token)->first();

        if (!$shop) {
            return  view('emails.verifystatus', ['status' => 'reject', 'path_link' => url('http://localhost:5173/dashboard/shop')]);
        }

        $shop->verification_token = null;
        $shop->email_verified_at = now();
        $shop->save();

        return view('emails.verifystatus', ['status' => 'success', 'path_link' => url('http://localhost:5173/dashboard/shop')]);
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
            'phone' => $request->get('shop_phone'),
            'address' => $request->get('address'),
            'description' => $request->get('description'),
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

    public function updatePassword(UpdatePasswordRequest $request, Shop $shop)
    {
        Gate::authorize('update', $shop);
        $new_password = $request->new_password;
        if (Hash::check($new_password, $shop->password)) {
            return response()->json([
                'message' => 'New password must differ from the old'
            ])->setStatusCode(400);
        }
        $shop->update([
            'password' => Hash::make($new_password)
        ]);
        return IdResource::make($shop);
    }

    public function updateAvatar(UpdateImageRequest $request, Shop $shop)
    {
        Gate::authorize('update', $shop);
        if ($request->hasFile('image')) {
            $file = $request->image;
            $filename = now()->format('Y-m-d_H:i:s.u') . '.png';
            $path = 'shop_images/'. $shop->id .'/'. $filename;
            Storage::disk('s3')->put($path, file_get_contents($file), 'private');
            $uri = str_replace('/', '+', $path);
            $shop->update([
                'image_url' => env("APP_URL") . 'api/images/' . $uri
            ]);
        }
        return IdResource::make($shop);
    }

    public function updateIsOpen(Request $request, Shop $shop)
    {
        Gate::authorize('update', $shop);
        $shop->update([
            'is_open' => !$shop->is_open
        ]);
        return IdResource::make($shop);
    }
}
