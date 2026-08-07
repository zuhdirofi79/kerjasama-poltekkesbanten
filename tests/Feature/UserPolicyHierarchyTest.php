<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_sa2_cannot_update_or_delete_sa1(): void
    {
        $sa1 = User::factory()->superadmin1()->create([
            'email' => 'webmaster@poltekkesbanten.ac.id',
        ]);
        $sa2 = User::factory()->superadmin2()->create([
            'email' => 'rifaldi.tri@poltekkesbanten.ac.id',
        ]);

        $this->assertFalse($sa2->can('update', $sa1));
        $this->assertFalse($sa2->can('delete', $sa1));

        $this->actingAs($sa2)
            ->put(route('admin.users.update', $sa1), [
                'name' => 'Hacked',
                'email' => $sa1->email,
            ])
            ->assertForbidden();

        $this->actingAs($sa2)
            ->delete(route('admin.users.destroy', $sa1))
            ->assertForbidden();
    }

    public function test_sa1_can_update_sa2(): void
    {
        $sa1 = User::factory()->superadmin1()->create();
        $sa2 = User::factory()->superadmin2()->create();

        $this->assertTrue($sa1->can('update', $sa2));

        $this->actingAs($sa1)
            ->put(route('admin.users.update', $sa2), [
                'name' => 'Updated SA2',
                'email' => $sa2->email,
                'role' => UserRole::Superadmin2->value,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame('Updated SA2', $sa2->fresh()->name);
    }
}
