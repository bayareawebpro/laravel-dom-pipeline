<?php

namespace BayAreaWebPro\DomPipeline;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BayAreaWebPro\DomPipeline\DomPipelineService
 * @method static DomPipelineService make(string|null $html, array $pipes = [])
 */
class DomPipeline extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'dom-pipeline';
    }
}
