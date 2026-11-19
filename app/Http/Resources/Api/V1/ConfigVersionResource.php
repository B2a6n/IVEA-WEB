<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\ConfigVersion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ConfigVersion
 */
final class ConfigVersionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'version' => $this->version,
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
