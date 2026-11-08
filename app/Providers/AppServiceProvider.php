<?php

namespace App\Providers;

use App\Models\BusinessSetting;
use Illuminate\Support\Facades\View;
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
        View::composer([
            'dashboard',
            'categories.*',
            'products.*',
            'sales.*',
            'inventory.*',
            'reports.*',
            'business-settings.*',
        ], function ($view) {
            $view->with('currencySymbol', BusinessSetting::query()->value('currency_symbol') ?? 'Q');
        });
    }
}
