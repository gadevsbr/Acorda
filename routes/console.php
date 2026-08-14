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

Schedule::command('identity:generate-candidates')
    ->dailyAt('04:15')
    ->withoutOverlapping(30)
    ->onOneServer();

Schedule::command('collect:prefeitura-expenses --max-pages=20 --per-page=100')
    ->dailyAt('05:00')
    ->withoutOverlapping(120)
    ->onOneServer();

foreach (['fornecedores', 'contratos', 'licitacoes', 'fiscais-contrato'] as $index => $resource) {
    Schedule::command("collect:prefeitura-procurement {$resource} --max-pages=20 --per-page=100")
        ->dailyAt('05:'.str_pad((string) (($index + 1) * 10), 2, '0', STR_PAD_LEFT))
        ->withoutOverlapping(120)
        ->onOneServer();
}
