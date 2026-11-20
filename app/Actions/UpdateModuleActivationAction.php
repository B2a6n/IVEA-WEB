<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ModuleActivationData;
use App\Exceptions\StaleModuleConfigurationException;
use App\Models\ConfigVersion;
use App\Models\GameModule;
use App\Models\User;
use App\Services\MobileConfigService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class UpdateModuleActivationAction
{
    // Transaction-scoped Postgres advisory lock that serializes every config publication.
    private const int PUBLISH_LOCK_KEY = 4_820_301;

    /**
     * Applies the requested is_active states and publishes a new config version with a snapshot of every
     * module, so the mobile app picks the change up on its next sync. Returns null when nothing changed:
     * an unchanged save must not force devices to resync.
     *
     * @throws StaleModuleConfigurationException when another version was published after the form was rendered.
     */
    public function execute(ModuleActivationData $data, User $admin): ?ConfigVersion
    {
        $version = DB::transaction(function () use ($data, $admin): ?ConfigVersion {
            $this->acquirePublishLock();

            $latestVersion = ConfigVersion::query()->max('version');
            $latestVersion = $latestVersion === null ? null : (int) $latestVersion;

            if ($latestVersion !== $data->baseVersion) {
                throw StaleModuleConfigurationException::since($data->baseVersion, (int) $latestVersion);
            }

            $changed = $this->changedStates($data);

            if ($changed->isEmpty()) {
                return null;
            }

            foreach ([true, false] as $isActive) {
                $ids = $changed->filter(fn (bool $state) => $state === $isActive)->keys();

                if ($ids->isNotEmpty()) {
                    GameModule::query()->whereKey($ids->all())->update(['is_active' => $isActive]);
                }
            }

            return $this->publishVersion($admin, $changed, ($latestVersion ?? 0) + 1);
        });

        if ($version !== null) {
            Cache::forget(MobileConfigService::CACHE_KEY);
        }

        return $version;
    }

    /**
     * Unlike a row lock, the advisory lock also holds when config_versions is empty or when two admins
     * change different modules, so version numbers never collide on unique(version).
     */
    private function acquirePublishLock(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::select('select pg_advisory_xact_lock(?)', [self::PUBLISH_LOCK_KEY]);
        }
    }

    /**
     * New is_active state of each module whose requested state differs from the stored one.
     *
     * @return Collection<int, bool>
     */
    private function changedStates(ModuleActivationData $data): Collection
    {
        return GameModule::query()
            ->whereKey(array_keys($data->states))
            ->pluck('is_active', 'id')
            ->filter(fn (bool $current, int $id) => $current !== $data->states[$id])
            ->map(fn (bool $current) => ! $current);
    }

    /**
     * Numbered after every row, drafts included, to respect unique(version).
     *
     * @param  Collection<int, bool>  $changed
     */
    private function publishVersion(User $admin, Collection $changed, int $version): ConfigVersion
    {
        $snapshot = GameModule::query()->orderBy('sort_order')->orderBy('id')->pluck('is_active', 'slug')->all();
        $enabled = $changed->filter()->count();

        return ConfigVersion::query()->create([
            'version' => $version,
            'payload' => [...(ConfigVersion::current()?->payload ?? []), 'modules' => $snapshot],
            'notes' => sprintf(
                'Control de Módulos: %d habilitados, %d deshabilitados.',
                $enabled,
                $changed->count() - $enabled,
            ),
            'published_by' => $admin->id,
            'published_at' => now(),
        ]);
    }
}
