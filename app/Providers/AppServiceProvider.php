<?php

namespace App\Providers;

use App\Support\SriLankanDate;
use Carbon\Carbon;
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
