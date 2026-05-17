<?php

namespace App\Domain\Cabinet\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\Cabinet\Models\CourseCategory
 */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'node_id' => (int)$this->node_id,
            /**
             * @var \App\Domain\Cabinet\Enums\CategoryParentCodeEnum
             */
            'parent_code' => $this->parent_code,
            'code' => $this->code,
            'name' => $this->name,
        ];
    }
}
