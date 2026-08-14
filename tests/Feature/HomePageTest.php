<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_installation_reports_zero_counts_without_claiming_published_data(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->where('stats.organizations', 0)
                ->where('stats.people', 0)
                ->where('stats.activeEmployments', 0)
                ->where('stats.positions', 0)
                ->where('stats.payrollRecords', 0)
                ->where('stats.netPayrollCents', 0)
                ->where('stats.payrollReference', null)
            );
    }
}
