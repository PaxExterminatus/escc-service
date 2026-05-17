<?php

namespace App\Domain\Cabinet\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\Cabinet\Models\Course
 */
class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int)$this->id,
            'node_id' => (int)$this->node_id,
            'client_id' => (int)$this->client_id,
            'name' => $this->name,
            /**
             * @var \App\Domain\Cabinet\Enums\CourseStatusEnum
             */
            'status' => $this->status,
            'lessons' => LessonResource::collection($this->lessons),
            'categories' => CategoryResource::collection($this->categories),
        ];
    }
}
