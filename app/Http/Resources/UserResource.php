<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'is_super_admin' => $this->isSuperAdmin(),
            'is_active' => $this->is_active,
            'employee' => $this->whenLoaded('employee', fn (): array => [
                'id' => $this->employee->id,
                'name' => $this->employee->name,
                'professional_email' => $this->employee->professional_email,
            ]),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
