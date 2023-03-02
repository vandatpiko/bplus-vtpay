<?php

namespace VandatPiko\BplusVTPay;

use GuzzleHttp\Client;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use VandatPiko\BplusVTPay\Contracts\BplusVTPayContract;

class BplusVTPayServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        if (function_exists('config_path')) {
            $this->publishes([
                __DIR__ . '/../config/bplusvtpay.php' => config_path('bplusvtpay.php'),
            ], 'bplusvtpay');

            $this->publishes([
                __DIR__ . '/../database/migrations/create_bplus_vtpays_table.php' => $this->getMigrationFileName('create_bplus_vtpays_table.php')
            ], 'bplusvtpay_migration');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/bplusvtpay.php', 'bplusvtpay');

        $this->app->singleton(BplusVTPayContract::class, function ($app) {

            @$explodeGuard = explode('|', config('bplusvtpay.guard'));
            foreach ($explodeGuard as $guard) {
                if (auth($guard)->check()) {
                    return new BplusVTPay(new Client([
                        'time_out'  => config('bplusvtpay.time_out'),
                        'http_errors' => false
                    ]), auth($guard));
                }
                return new BplusVTPay(new Client([
                    'time_out'  => config('bplusvtpay.time_out'),
                    'http_errors' => false
                ]), null);
            }
        });
    }

    public function getMigrationFileName($migrationFileName)
    {
        $timestamp = date('Y_m_d_His', time());

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make($this->app->databasePath() . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $migrationFileName) {
                return $filesystem->glob($path . '*_' . $migrationFileName);
            })
            ->push($this->app->databasePath() . "/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }
}
