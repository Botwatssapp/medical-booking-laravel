<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Expire automatiquement les rendez-vous dont la date est dépassée :
 *  - pending  → cancelled  (médecin n'a jamais confirmé avant l'heure)
 *  - accepted → missed     (confirmé mais patient/médecin absent)
 *
 * Tourne toutes les minutes pour être réactif (passer à ->hourly() en prod).
 */
Schedule::command('appointments:expire')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
