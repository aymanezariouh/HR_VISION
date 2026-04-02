<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'title' => $this->title,
            'type' => $this->type,
            'file_path' => $this->file_path,
            'uploaded_at' => $this->uploaded_at?->toJSON(),
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
