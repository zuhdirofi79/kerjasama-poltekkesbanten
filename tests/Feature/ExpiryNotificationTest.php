<?php

namespace Tests\Feature;

use App\Enums\CooperationExpiryStatus;
use App\Models\User;
use App\Services\CooperationExpiryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\CreatesCooperationHierarchy;
use Tests\TestCase;

class ExpiryNotificationTest extends TestCase
{
    use CreatesCooperationHierarchy;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_expiry_status_boundaries(): void
    {
        Carbon::setTestNow('2026-08-18');
        $service = new CooperationExpiryService;

        $this->assertSame(
            CooperationExpiryStatus::Active,
            $service->status(Carbon::parse('2026-09-18')),
        );
        $this->assertSame(
            CooperationExpiryStatus::ExpiringSoon,
            $service->status(Carbon::parse('2026-09-17')),
        );
        $this->assertSame(
            CooperationExpiryStatus::ExpiringSoon,
            $service->status(Carbon::parse('2026-08-18')),
        );
        $this->assertSame(
            CooperationExpiryStatus::Expired,
            $service->status(Carbon::parse('2026-08-17')),
        );
        $this->assertNull($service->status(null));
    }

    public function test_guest_cannot_open_expiry_admin_list(): void
    {
        $this->get(route('admin.expiry.index'))
            ->assertRedirect(route('login'));
    }

    public function test_visitor_cannot_open_expiry_admin_list(): void
    {
        $visitor = User::factory()->visitor()->create();

        $this->actingAs($visitor)
            ->get(route('admin.expiry.index'))
            ->assertForbidden();
    }

    public function test_admin_bell_shows_expiring_and_expired_not_active(): void
    {
        Carbon::setTestNow('2026-08-18');
        ['year' => $year] = $this->makeHierarchy();
        $this->makeDocument($year, [
            'title' => 'Akan Habis',
            'cooperation_end_date' => '2026-09-01',
        ]);
        $this->makeDocument($year, [
            'title' => 'Sudah Habis',
            'cooperation_end_date' => '2026-08-01',
        ]);
        $this->makeDocument($year, [
            'title' => 'Masih Lama',
            'cooperation_end_date' => '2027-01-01',
        ]);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Notifikasi Masa Berlaku')
            ->assertSee('Akan Habis')
            ->assertSee('Sudah Habis')
            ->assertDontSee('Masih Lama');
    }
}
