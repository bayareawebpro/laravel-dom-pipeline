<?php declare(strict_types=1);

namespace BayAreaWebPro\DomPipeline;

use DOMDocument;
use Illuminate\Pipeline\Pipeline;

class DomPipelineService
{
    protected Pipeline $pipeline;
    protected array $pipes;

    public static function make(?string $html = null, array $pipes = [])
    {
        return app(static::class, [
            'html'  => $html,
            'pipes' => $pipes,
        ])->process($html);
    }

    public function __construct(Pipeline $pipeline, ?string $html = null, array $pipes = [])
    {
        $this->pipeline = $pipeline;
        $this->pipes = $pipes;
    }

    public function process(?string $html = null)
    {
        if (empty($html)) return $html;

        return (string)$this->pipeline
            ->send($this->getDom($html))
            ->through($this->pipes)
            ->via('handle')
            ->then(function (DOMDocument $dom) {
                return $this->toHtml($dom);
            });
    }

    protected function toHtml(DOMDocument $dom): string
    {
        $html = $dom->saveHTML($dom->getElementsByTagName('body')->item(0));
        return str_replace(['<body>', '</body>'], '', $html);
    }

    protected static function getDom(string $html): DOMDocument
    {
        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();
        return $doc;
    }
}
