<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemCollection;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Repositories\ItemRepository;
use Illuminate\Http\Request;

class ItemController extends Controller
{

    public function __construct(
        private ItemRepository $itemRepository
    )
    {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request,int $shop_id)
    {
        $items = $this->itemRepository->getAllItemByShopID($shop_id);
        return new ItemCollection($items);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(){}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request,  $shop_id)
    {
        $validated = $request->validate([
            'name' => ['required', 'min:3', 'max:255'],
            'description' => ['required', 'min:3', 'max:255'],
            'price' => ['required'],
            'is_available' => ['required'],
        ]);
        $item = $this->itemRepository->create([
            'name'=> $validated["name"],
            'description'=> $validated["description"],
            'price'=> $validated["price"],
            'shop_id' => $shop_id,
            'is_available' => $validated["is_available"],
        ]);
        return new ItemResource($item);
    }

    /**
     * Display the specified resource.
     */
    public function show($shop_id, Item $item)
    {
        $id = $item->id;
        $item = $this->itemRepository->getItemByItemID($shop_id, $id);
        return new ItemResource($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item){}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $shop_id, Item $item)
    {

        //check if there is item in that shop
        $item = $this->itemRepository->getItemByItemID($shop_id, $item->id);

        //if there is no item in that shop
        if(!$item){
            return response()->json([
                "message" => "Item not found"
            ], 404);
        }

        $validate= [];

        //validate request first
        if($request->isMethod("put")){
            $validate = $request->validate([
                "name" => "required|string",
                "description" => "required|string",
                "price" => "required|integer",
                "item_image_url" => "required|string",
                "is_available" => "required|boolean",
            ]);
        }

        if($request->isMethod("patch")){
            $validate = $request->validate([
                "name" => "string|nullable",
                "description" => "string|nullable",
                "price" => "integer|nullable",
                "is_available" => "boolean|nullable",
                "item_image_url" => "string|nullable",
            ]);
        }
        //update section
        $this->itemRepository->update(
            $validate
        , $item->id);
        return new ItemResource($this->itemRepository->getItemByItemID($shop_id, $item->id));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item, $shop_id)
    {
        $item = $this->itemRepository->getItemByItemID($shop_id, $item->id);
        if(!$item){
            return response()->json(["message" => "Item not found"], 404);
        }

        $this->itemRepository->delete($item->id);
        return response()->json(["message" => "Item deleted successfully"], 200);
    }
}
