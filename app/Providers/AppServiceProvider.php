<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use App\Services\SettingService;
use Throwable;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            $settings = SettingService::allMerged();

            View::share('appSettings', $settings);

            if (!empty($settings['app_name'])) {
                config(['app.name' => $settings['app_name']]);
            }
        } catch (Throwable $e) {
            $settings = SettingService::defaults();

            View::share('appSettings', $settings);

            if (!empty($settings['app_name'])) {
                config(['app.name' => $settings['app_name']]);
            }
        }

        //
    }
}
