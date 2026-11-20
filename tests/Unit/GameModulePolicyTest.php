<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Policies\GameModulePolicy;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class GameModulePolicyTest extends TestCase
{
    #[Test]
    public function only_active_admins_can_manage_modules(): void
    {
        $policy = new GameModulePolicy;

        $this->assertTrue($policy->manage(User::factory()->admin()->make()));
        $this->assertFalse($policy->manage(User::factory()->admin()->inactive()->make()));
        $this->assertFalse($policy->manage(User::factory()->tutor()->make()));
        $this->assertFalse($policy->manage(User::factory()->student()->make()));
    }
}
