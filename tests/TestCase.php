<?php

namespace BayAreaWebPro\DomPipeline\Tests;

use BayAreaWebPro\DomPipeline\DomPipeline;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as BaseTestCase;
use BayAreaWebPro\DomPipeline\DomPipelineServiceProvider;

abstract class TestCase extends BaseTestCase
{

    protected function getPackageProviders($app)
    {
        return [DomPipelineServiceProvider::class];
    }

    /**
     * Load package alias
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'DomPipeline' => DomPipeline::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->app['config']->set('app.debug', true);
        $this->app['config']->set('app.key', Str::random(32));
    }
}
