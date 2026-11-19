<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\SyncProgressScoresAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SyncScoresRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class MobileSyncController extends Controller
{
    public function __invoke(SyncScoresRequest $request, SyncProgressScoresAction $action): JsonResponse
    {
        return ApiResponse::success($action->execute($request->toDto())->toArray());
    }
}
