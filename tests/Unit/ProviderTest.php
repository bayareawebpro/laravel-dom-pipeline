<?php declare(strict_types=1);

namespace BayAreaWebPro\DomPipeline\Tests\Unit;

use BayAreaWebPro\DomPipeline\DomPipelineServiceProvider;
use BayAreaWebPro\DomPipeline\DomPipelineService;
use BayAreaWebPro\DomPipeline\Tests\TestCase;

class ProviderTest extends TestCase
{
    public function test_provider_is_registered()
    {
        $this->assertInstanceOf(
            DomPipelineServiceProvider::class,
            $this->app->getProvider(DomPipelineServiceProvider::class),
            'Provider is registered with container.');
    }

    public function test_container_can_resolve_instance()
    {
        $this->assertInstanceOf(
            DomPipelineService::class,
            $this->app->make('dom-pipeline'),
            'Container can make instance of service.');
    }

    public function test_facade_can_resolve_instance()
    {
        $this->assertInstanceOf(
            DomPipelineService::class,
            \DomPipeline::getFacadeRoot(),
            'Facade can make instance of service.');
    }

    public function test_service_can_be_resolved()
    {
        $instance = app('dom-pipeline');
        $this->assertTrue(($instance instanceof DomPipelineService));
    }
}
