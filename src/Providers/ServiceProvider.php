<?php
/**
 * @package     IdnPlay\Vock88\Providers - ServiceProvider
 * @author      singkek
 * @copyright   Copyright(c) 2019
 * @version     1
 * @created     2020-01-24
 * @updated     2020-01-24
 **/

namespace IdnPlay\Vock88\Providers;

use IdnPlay\Vock88\Vock88;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Contracts\Container\Container as Application;
use Illuminate\Foundation\Application as LaravelApplication;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->setupConfig($this->app);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton('vock88', function () {
            $config = app('config')->get('vock88')['voucher88'];
            return new Vock88($config);
        });

        $this->app->alias('vock88',Vock88::class);
    }

    /**
     * Setup the config.
     *
     * @param \Illuminate\Contracts\Container\Container $app
     */
    protected function setupConfig(Application $app)
    {
        $source = __DIR__.'/../Config/config.php';

        if ($app instanceof LaravelApplication && $app->runningInConsole()) {
            $this->publishes([$source => config_path('vock88.php')],'vock88-config');
        } elseif ($app instanceof LumenApplication) {
            $app->configure('vock88');
        }

        $this->publishes([
            __DIR__.'/../Migration' => database_path('migrations')
        ], 'vock88-migrations');

        $this->mergeConfigFrom($source, 'vock88');
    }
}
