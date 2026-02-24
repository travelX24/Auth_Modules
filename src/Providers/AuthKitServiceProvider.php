<?php

namespace Athka\AuthKit\Providers;

use Illuminate\Support\ServiceProvider;

class AuthKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/authkit.php', 'authkit');
    }

    public function boot(): void
    {
        // ✅ publishing يكون متاح دائماً
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Config/authkit.php' => config_path('authkit.php'),
            ], 'authkit-config');

            $this->publishes([
                __DIR__ . '/../Resources/views' => resource_path('views/vendor/authkit'),
            ], 'authkit-views');
        }

        // ✅ لا تفعل أي شيء إلا إذا enabled=true (من config المشروع)
        if (!config('authkit.enabled', false)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        if (config('authkit.api.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'authkit');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'authkit');
    }
}
