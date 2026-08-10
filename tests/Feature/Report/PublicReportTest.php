<?php

namespace Tests\Feature\Report;

use App\Enums\ReportStatusEnum;
use App\Models\Link;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_report_page(): void
    {
        $this->get(route('report'))
            ->assertOk()
            ->assertSee(__('app.report.heading'));
    }

    public function test_guest_can_submit_report_for_existing_link_with_full_url(): void
    {
        $user = User::factory()->create();
        $link = Link::factory()->create([
            'user_id' => $user->id,
            'short_code' => 'Ab12Cd34',
            'destination' => 'https://example.com/reported',
        ]);

        Livewire::test('pages::report')
            ->set('linkInput', url('/Ab12Cd34'))
            ->set('reason', 'This link contains phishing content')
            ->call('submitReport')
            ->assertHasNoErrors()
            ->assertSet('issuedTrackingCode', fn (?string $code): bool => is_string($code) && strlen($code) === 10)
            ->assertSee(__('app.report.submitted'));

        $this->assertDatabaseHas('reports', [
            'link_id' => $link->id,
            'status' => ReportStatusEnum::Pending->value,
            'reason' => 'This link contains phishing content',
        ]);
    }

    public function test_guest_can_submit_report_with_bare_short_code(): void
    {
        $link = Link::factory()->create(['short_code' => 'Xy98Zt76']);

        Livewire::test('pages::report')
            ->set('linkInput', 'Xy98Zt76')
            ->set('reason', 'Spam destination for this short link')
            ->call('submitReport')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('reports', [
            'link_id' => $link->id,
            'status' => ReportStatusEnum::Pending->value,
        ]);
    }

    public function test_submit_fails_when_link_does_not_exist(): void
    {
        Livewire::test('pages::report')
            ->set('linkInput', 'NoExists1')
            ->set('reason', 'This link should not exist here')
            ->call('submitReport')
            ->assertHasErrors(['linkInput']);

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_guest_can_track_report_by_tracking_code(): void
    {
        $report = Report::factory()->pending()->create([
            'tracking_code' => 'TRACKCODE1',
            'reason' => 'Malicious redirect reported by user',
        ]);

        Livewire::test('pages::report')
            ->set('trackingCode', 'TRACKCODE1')
            ->call('track')
            ->assertHasNoErrors()
            ->assertSet('trackedStatus', ReportStatusEnum::Pending->label())
            ->assertSet('trackedReason', $report->reason);
    }

    public function test_track_fails_for_unknown_code(): void
    {
        Livewire::test('pages::report')
            ->set('trackingCode', 'UNKNOWNCOD')
            ->call('track')
            ->assertSet('trackError', __('app.report.tracking_not_found'));
    }
}
