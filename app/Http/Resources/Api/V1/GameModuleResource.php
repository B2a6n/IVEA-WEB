<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\GameModule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GameModule
 */
final class GameModuleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'subject_area' => $this->subject_area,
            'difficulty_level' => $this->difficulty_level,
        ];
    }
}
