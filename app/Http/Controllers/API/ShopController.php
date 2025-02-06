<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShopCollection;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use App\Repositories\ShopRepository;
use Illuminate\Http\Request;
use function Laravel\Prompts\warning;

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
        $shops = $this->shopRepository->getAll();
        return new ShopCollection($shops);
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
        $validated = $request->validate([
            'name' => ['required', 'min:3', 'max:255', 'unique:shops,name'],
            'address' => ['required', 'min:3', 'max:255'],
            'shop_phone' => ['required', 'min:3', 'max:255'],
            'description' => ['required', 'min:3', 'max:255'],
            'is_open' => ['required', 'boolean'],
            'approve_status' => ['required', 'boolean'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $shop = $this->shopRepository->create([
            'name' => $request->get('name'),
            'address' => $request->get('address'),
            'shop_phone' => $request->get('shop_phone'),
            'description' => $request->get('description'),
            'is_open' => $request->get('is_open'),
            'approve_status' => $request->get('approve_status'),
            'user_id' => $request->get('user_id'),
        ]);

        return new ShopResource($shop);
    }

    /**
     * Display the specified resource.
     */
    public function show(Shop $shop)
    {
        return new ShopResource($shop);
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
    public function update(Request $request, Shop $shop)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop)
    {
        //
    }
}
