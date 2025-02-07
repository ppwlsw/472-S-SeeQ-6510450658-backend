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
       return[
           'id' => $this->id,
           'email' => $this->email,
            'name' => $this->name,
           'role' => $this->role,
           'gender' => $this->gender,
           'address' => $this->address,
           'user_phone' => $this->user_phone,
           'birth_date' => $this->birth_date,
          ];
    }
}
