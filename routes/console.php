<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('collect:prefeitura-payroll --max-pages=20 --per-page=100')
    ->dailyAt('02:15')
    ->withoutOverlapping(120)
    ->onOneServer();
