<?php declare(strict_types=1);

namespace BayAreaWebPro\DomPipeline;

use DOMDocument;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\App;

class DomPipelineService
{
    protected Pipeline $pipeline;

    protected array $pipes;

    public function __construct(Pipeline $pipeline, array $pipes = [])
    {
        $this->pipeline = $pipeline;
        $this->pipes = $pipes;
    }

    public static function make(?string $html = null, array $pipes = [])
    {
        return App::make(static::class, compact('pipes'))->process($html);
    }

    protected function process(?string $html = null): ?string
    {
        if (empty($html)) return $html;

        return (string)$this->pipeline
            ->send(DomParser::make($html))
            ->through($this->pipes)
            ->via('handle')
            ->then(function (DOMDocument $dom) {
                return DomParser::getBodyHtml($dom);
            });
    }
}
