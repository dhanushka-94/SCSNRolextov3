<?php

namespace App\Providers;

use App\Support\SriLankanDate;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        date_default_timezone_set(SriLankanDate::timezone());
        Carbon::setLocale(config('app.locale', 'en'));

        // JSON (API) timestamps are written in Sri Lanka time, e.g. 2026-11-24T12:30:23+05:30,
        // instead of Laravel's default UTC "Z" form.
        Carbon::serializeUsing(fn (CarbonInterface $date) => $date->copy()
            ->timezone(SriLankanDate::timezone())
            ->format('Y-m-d\TH:i:sP'));

        Paginator::defaultView('vendor.pagination.earth');
        Paginator::defaultSimpleView('vendor.pagination.earth');

        $releases = config('changelog.releases', []);
        $latestVersion = is_array($releases) && isset($releases[0]['version'])
            ? (string) $releases[0]['version']
            : '0.0.1';

        config([
            'app.version' => $latestVersion,
        ]);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            URL::forceRootUrl(config('app.url'));
        }
    }
}
