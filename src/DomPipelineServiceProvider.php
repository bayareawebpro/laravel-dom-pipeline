<?php

namespace BayAreaWebPro\DomPipeline;

use Illuminate\Support\ServiceProvider;

class DomPipelineServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind('dom-pipeline', DomPipelineService::class);
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return ['dom-pipeline'];
    }
}
