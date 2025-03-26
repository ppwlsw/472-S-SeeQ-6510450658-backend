<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateShopRequest;
use App\Http\Requests\NearbyShopsRequest;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateImageRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Http\Resources\ItemResource;
use App\Http\Resources\ShopResource;
use App\Http\Resources\IdResource;
use App\Http\Resources\UrlResource;
use App\Mail\ShopVerificationEmail;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\ShopRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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

    public function getAllShopWithTrashed()
    {
        Gate::authorize('viewAny', Shop::class);
        $shops = $this->shopRepository->getAllShopWithTrashed();
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
                'email' => strtolower($request->email),
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => 'SHOP'
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
                $path = 'shops/'. $shop->id .'/images/logos/'. $filename;
                Storage::disk('s3')->put($path, file_get_contents($file), 'private');
                $uri = str_replace('/', '+', $path);
                $shop->update([
                    'image_url' => env("APP_URL") . '/api/images/' . $uri
                ]);
                $user->update([
                    'image_url' => env("APP_URL") . '/api/images/' . $uri
                ]);
            }

            Mail::to($user->email)->send(new ShopVerificationEmail($shop, $user));
            return [
                'user' => $user,
                'shop' => $shop
            ];
        });
        $shop = $result["shop"];

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
            'phone' => $request->get('phone'),
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

    public function restore($id)
    {
        $shop = $this->shopRepository->getByIdWithTrashed($id);
        Gate::authorize('restore', $shop);
        $shop->restore();
        return response()->json(['message' => 'Shop restored successfully!'])->setStatusCode(200);
    }

    public function updateLocation(Request $request, int $id)
    {
        $shop = $this->shopRepository->getById($id);
        Gate::authorize('update', $shop);
        $shop->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address
        ]);

        return IdResource::make($shop)->response()->setStatusCode(200);
    }

    public function updateAvatar(UpdateImageRequest $request, Shop $shop)
    {
        Gate::authorize('update', $shop);
        if ($request->hasFile('image')) {
            $file = $request->image;
            $filename = now()->format('Y-m-d_H:i:s.u') . '.png';
            $path = 'shops/'. $shop->id .'/images/logos/'. $filename;
            Storage::disk('s3')->put($path, file_get_contents($file), 'public');
            $uri = str_replace('/', '+', $path);
            $shop->update([
                'image_url' => env("APP_URL") . '/api/images/' . $uri
            ]);
        }
        return UrlResource::make((object)[
            'url' => $shop->image_url
        ]);
    }


    public function updateIsOpen(Request $request, Shop $shop)
    {
        Gate::authorize('update', $shop);
        $shop->update([
            'is_open' => !$shop->is_open
        ]);
        return IdResource::make($shop)->response()->setStatusCode(200);
    }

    public function showNearbyShops(NearbyShopsRequest $request)
    {

        Gate::authorize('view', Shop::class);


        $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]
        );

        $latitude = $request->get('latitude');
        $longitude = $request->get("longitude");

        if (!$latitude || !$longitude) {
            return response()->json(['message' => 'Latitude and Longitude are required'], 400);
        }

        $shops = $this->shopRepository->getNearbyShops($latitude, $longitude);

        return ShopResource::collection($shops);
    }

    public function showItem(Shop $shop)
    {
       $item = $shop->item()->first();
       if (!$item) {
           return response()->json([
               'data' => []
           ]);
       }
       return response()->json([
           'data' => [
               'id' => $item->id,
              'api_url'  => $item->api_url,
           ]
       ]);
    }

    public function showShopItems(Request $request, Shop $shop)
    {
        Gate::authorize('view', $shop);
        $item = $shop->item()->first();
        if (!$item->api_key) {
           return response()->json([
               'data' => []
           ]);
        }
        $response = Http::withHeaders([
            'Api-key' => decrypt($item->api_key),
            'Name' => 'seeq-ri-api1'
        ])->get($item->api_url);
        if (!$response->successful()) {
            return response()->json(['error' => "Api is not found"])->setStatusCode(400);
        }
        return $response->json();
    }

    public function storeItem(StoreItemRequest $request, Shop $shop)
    {
        Gate::authorize('create', Shop::class);
        if ($shop->item()->first()) {
            return response()->json([
                'error' => "Api already exists"
            ], 400);
        }
        $shop->item()->create([
            'api_url' => $request->get('api_url'),
            'api_key' => encrypt($request->get('api_key'))
        ]);
        return IdResource::make($shop)->response()->setStatusCode(201);
    }

    public function updateItem(UpdateItemRequest $request, Shop $shop)
    {
        Gate::authorize('update', $shop);
        $shop->item()->update([
            'api_url' => $request->get('api_url'),
            'api_key' => encrypt($request->get('api_key'))
        ]);
        return response([
            'data' => [
                'id' => $shop->item()->first()->id,
            ]
        ])->setStatusCode(200);
    }

}
