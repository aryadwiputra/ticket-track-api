<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
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
            'code' => $this->code,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value, // Access enum value
            'priority' => $this->priority->value, // Access enum value
            'created_by' => new UserResource($this->whenLoaded('createdBy')), // Load if available
            'assigned_to' => new UserResource($this->whenLoaded('assignedTo')), // Load if available
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'completed_at' => $this->completed_at ? $this->completed_at->toIso8601String() : null,
        ];
    }
}