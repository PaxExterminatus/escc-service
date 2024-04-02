<?php

namespace App\Domain\Cabinet\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\Cabinet\Models\ClientCourseLesson
 */
class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int)$this->id,
            'client_id' => (int)$this->client_id,
            'course_id' => (int)$this->course_id,
            'node_id' => (int)$this->node_id,
            'name' => $this->name,
        ];
    }
}
