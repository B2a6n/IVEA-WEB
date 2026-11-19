<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ConfigVersionResource;
use App\Http\Resources\Api\V1\GameModuleResource;
use App\Http\Responses\ApiResponse;
use App\Services\MobileConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MobileConfigController extends Controller
{
    public function __invoke(Request $request, MobileConfigService $service): JsonResponse
    {
        $config = $service->current();

        return ApiResponse::success([
            'config_version' => $config->configVersion
                ? (new ConfigVersionResource($config->configVersion))->resolve($request)
                : null,
            'modules' => GameModuleResource::collection($config->modules)->resolve($request),
        ]);
    }
}
