<?php declare(strict_types=1);

namespace BayAreaWebPro\DomPipeline;

use DOMDocument;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\App;

class DomPipelineService
{
    public function __construct(
        protected Pipeline $pipeline,
        protected array $pipes = []
    ){
        //
    }
    public static function make(string|null $html, array $pipes = [])
    {
        return App::make(static::class, compact('pipes'))->process($html);
    }
    protected function process(string|null $html): ?string
    {
        if (empty($html)) return $html;

        return $this->pipeline
            ->send(DomParser::make($html))
            ->through($this->pipes)
            ->via('handle')
            ->then(function (DOMDocument $dom) {
                return DomParser::getBodyHtml($dom);
            });
    }
}
