<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateModuleActivationAction;
use App\Exceptions\StaleModuleConfigurationException;
use App\Http\Requests\Modules\UpdateModulesRequest;
use App\Services\ModuleControlService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class ModuleControlController extends Controller
{
    public function index(ModuleControlService $service): View
    {
        return view('modulos.index', ['overview' => $service->overview()]);
    }

    public function update(UpdateModulesRequest $request, UpdateModuleActivationAction $action): RedirectResponse
    {
        try {
            $version = $action->execute($request->toData(), $request->user());
        } catch (StaleModuleConfigurationException $e) {
            // Back to the index without old input: the page must reload with the other admin's state.
            throw ValidationException::withMessages(['base_version' => $e->getMessage()])
                ->redirectTo(route('modulos.index'));
        }

        $redirect = redirect()->route('modulos.index');

        return $version === null
            ? $redirect->with('notice', 'No hubo cambios que guardar.')
            : $redirect->with('status', "Cambios guardados. Versión activa: v{$version->version}.");
    }
}
