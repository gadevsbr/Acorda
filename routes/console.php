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

Schedule::command('collect:prefeitura-organizations --max-pages=10 --per-page=100')
    ->dailyAt('02:45')
    ->withoutOverlapping(120)
    ->onOneServer();

Schedule::command('collect:kbf-active-employees')
    ->dailyAt('03:15')
    ->withoutOverlapping(120)
    ->onOneServer();

Schedule::command('collect:kbf-payroll')
    ->dailyAt('03:45')
    ->withoutOverlapping(120)
    ->onOneServer();
