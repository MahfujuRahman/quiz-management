<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Modules\Management\WarehouseManagement\WarehouseProductOut\Models\WareHouseProductOutProductModel as WarehouseProductOut;
use App\Modules\Management\WarehouseManagement\WarehouseProductOut\Observer\WarehouseProductOutObserver;
use Illuminate\Support\Facades\View;
use App\Modules\Management\SettingManagement\WebsiteSettings\Models\Model as SettingTitleValue;

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
        Paginator::useBootstrap();
        // WarehouseProductOut::observe(WarehouseProductOutObserver::class);

        View::composer('*', function ($view) {
            $app_settings = SettingTitleValue::with([
                'setting_values' => function ($query) {
                    $query->select('id', 'title', 'value', 'setting_title_id');
                }
            ])
            ->select('id', 'title')
            ->get()->toArray();

            $GLOBALS['app_settings'] = $app_settings;
    
            $view->with([
                'app_settings' => $app_settings,
            ]);
        });
    }
}
