<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'user_id' => $this->user_id,
            'department_id' => $this->department_id,
            'name' => $this->name,
            'professional_email' => $this->professional_email,
            'phone' => $this->phone,
            'address' => $this->address,
            'position' => $this->position,
            'hire_date' => $this->hire_date?->toDateString(),
            'contract_type' => $this->contract_type,
            'status' => $this->status,
            'department' => $this->whenLoaded('department', fn (): array => [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ]),
            'user' => $this->whenLoaded('user', fn (): array => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'role' => $this->user->role,
            ]),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
