<?php

namespace BayAreaWebPro\DomPipeline\Tests;

use Illuminate\Support\Str;
use BayAreaWebPro\DomPipeline\DomPipeline;
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
}
