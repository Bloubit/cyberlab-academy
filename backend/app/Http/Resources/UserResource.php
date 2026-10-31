<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'avatar' => $this->avatar
                ? (str_starts_with($this->avatar, 'data:image/') ? $this->avatar : $this->avatar_url)
                : null,
            'bio' => $this->bio,
            'phone' => $this->phone,
            'country_code' => $this->country_code,
            'timezone' => $this->timezone,
            'language' => $this->language,
            'is_active' => $this->is_active,
            'total_xp' => $this->total_xp,
            'level' => $this->level,
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'is_admin' => $this->isAdmin(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
