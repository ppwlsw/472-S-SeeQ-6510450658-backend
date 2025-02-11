<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $image_uri = str_replace('/','+', $this->image_uri);
        return [
           'id' => $this->id,
           'email' => $this->email,
           'name' => $this->name,
           'role' => $this->role,
           'address' => $this->address,
           'phone' => $this->phone,
            'image_uri' => $image_uri
        ];
    }
}
