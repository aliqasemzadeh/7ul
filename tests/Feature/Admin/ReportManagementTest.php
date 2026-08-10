<?php

namespace Tests\Feature\Admin;

use App\Enums\ReportStatusEnum;
use App\Models\Link;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_admin_reports(): void
    {
        $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_view_reports(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_reports_list(): void
    {
        $admin = User::factory()->admin()->create();
        $report = Report::factory()->pending()->create([
            'tracking_code' => 'ADMINVIEW01',
            'reason' => 'Visible report reason text',
        ]);

        Livewire::actingAs($admin)
            ->test('pages::admin.report.index')
            ->assertSee($report->tracking_code)
            ->assertSee(__('app.admin.reports.heading'));
    }

    public function test_admin_can_accept_report_and_soft_delete_link(): void
    {
        $admin = User::factory()->admin()->create();
        $link = Link::factory()->create();
        $report = Report::factory()->pending()->create([
            'link_id' => $link->id,
        ]);

        Livewire::actingAs($admin)
            ->test('pages::admin.report.check', ['report' => $report])
            ->set('admin_note', 'Confirmed abuse')
            ->call('accept')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.reports.index'));

        $this->assertSoftDeleted($link);

        $report->refresh();
        $this->assertSame(ReportStatusEnum::Accepted, $report->status);
        $this->assertSame($admin->id, $report->reviewed_by);
        $this->assertSame('Confirmed abuse', $report->admin_note);
        $this->assertNotNull($report->reviewed_at);
    }

    public function test_admin_can_reject_report_without_deleting_link(): void
    {
        $admin = User::factory()->admin()->create();
        $link = Link::factory()->create();
        $report = Report::factory()->pending()->create([
            'link_id' => $link->id,
        ]);

        Livewire::actingAs($admin)
            ->test('pages::admin.report.check', ['report' => $report])
            ->call('reject')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.reports.index'));

        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'deleted_at' => null,
        ]);

        $report->refresh();
        $this->assertSame(ReportStatusEnum::Rejected, $report->status);
        $this->assertSame($admin->id, $report->reviewed_by);
    }

    public function test_reports_can_be_filtered_by_status(): void
    {
        $admin = User::factory()->admin()->create();
        $pending = Report::factory()->pending()->create([
            'tracking_code' => 'PENDINGCODE',
        ]);
        Report::factory()->accepted()->create([
            'tracking_code' => 'ACCEPTEDCD',
        ]);

        Livewire::actingAs($admin)
            ->test('pages::admin.report.index')
            ->set('status', ReportStatusEnum::Pending->value)
            ->assertSee($pending->tracking_code)
            ->assertDontSee('ACCEPTEDCD');
    }
}
