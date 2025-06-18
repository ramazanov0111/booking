<?php

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'lastname' => $this->lastname,
            'login' => $this->login,
            'email' => $this->email,
            'password' => $this->password,
            'phone' => $this->phone,
            'role' => $this->role,
            'deleted' => (boolean)$this->deleted,
            'created_at' => $this->created_at->format('d-m-Y H:i'),
            'updated_at' => $this->updated_at->format('d-m-Y H:i'),
        ];
    }
}
