<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateShopRequest;
use App\Http\Requests\UpdateImageRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Http\Resources\ShopResource;
use App\Http\Resources\IdResource;
use App\Mail\ShopVerificationEmail;
use App\Models\Shop;
use App\Repositories\ShopRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(
        private ShopRepository $shopRepository,
        private UserRepository $userRepository
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

        $result = DB::transaction(function () use ($request) {
            $user = $this->userRepository->create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'phone' => $request->phone
            ]);


            $shop = $this->shopRepository->create([
                'user_id' => $user->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
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
            return [
                'user' => $user,
                'shop' => $shop
            ];
        });

        $user = $result['user'];
        $shop = $result['shop'];

        Mail::to($user->email)->send(new ShopVerificationEmail($shop, $user));

        return IdResource::make($shop)->response()->setStatusCode(201);
    }



    /**
     * Display the specified resource.
     */
    public function show(Shop $shop)
    {
        Gate::authorize('view', Shop::class);
        return ShopResource::make($shop)->response()->setStatusCode(200);
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
        return IdResource::make($shop)->response()->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop)
    {
        Gate::authorize('delete', $shop);
        $shop->delete();
    }

    public function updateLocation(Request $request, int $id)
    {
        Gate::authorize('update', Shop::class);
        $shop = $this->shopRepository->getById($id);
        $shop->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);
        return IdResource::make($shop)->response()->setStatusCode(200);
    }

    public function updatePassword(UpdatePasswordRequest $request, Shop $shop)
    {
        Gate::authorize('update', $shop);
        $new_password = $request->new_password;
        if (Hash::check($new_password, $shop->password)) {
            return response()->json([
                'error' => 'New password must differ from the old'
            ])->setStatusCode(400);
        }
        $shop->update([
            'password' => Hash::make($new_password)
        ]);
        return IdResource::make($shop)->response()->setStatusCode(200);
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
        return IdResource::make($shop)->response()->setStatusCode(200);
    }

    public function updateIsOpen(Request $request, Shop $shop)
    {
        Gate::authorize('update', $shop);
        $shop->update([
            'is_open' => !$shop->is_open
        ]);
        return IdResource::make($shop)->response()->setStatusCode(200);
    }
}
