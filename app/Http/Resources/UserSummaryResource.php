<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'nip_nik' => $this->nip_nik,
            'company_name' => $this->company_name,
            'roles' => $this->whenLoaded('roles', fn () => $this->getRoleNames()),
        ];
    }
}
