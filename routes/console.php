<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('bank:send-statements')
    ->monthlyOn(1, '01:00')
    ->name('monthly-bank-statements')
    ->withoutOverlapping();