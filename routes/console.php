<?php

use Illuminate\Foundation\Inspiring;
use App\Enums\PolicyStatus;
use App\Models\Policy;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Schedule::call(fn () => app(\App\Services\QuoteService::class)->expireStaleQuotes())
    ->daily()->at('01:00');
Schedule::call(function () {
    $count = Policy::where('status', PolicyStatus::Active->value)
        ->where('end_date', '<', now()->toDateString())
        ->update(['status' => PolicyStatus::Expired->value]);

    logger()->info("Expired {$count} policies.");
})->daily()->at('02:00');    
