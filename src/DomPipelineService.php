<?php declare(strict_types=1);

namespace BayAreaWebPro\DomPipeline;

use DOMDocument;
use Illuminate\Pipeline\Pipeline;

class DomPipelineService
{
    public static function make(?string $html = null, array $pipes = []): string
    {
        return (string) app(Pipeline::class)
            ->send(static::getDom($html))
            ->through($pipes)
            ->via('handle')
            ->then(function (DOMDocument $dom) {
                $html = $dom->saveHTML($dom->getElementsByTagName('body')->item(0));
                return str_replace(['<body>','</body>'],'',$html);
            });
    }

    protected static function getDom(?string $html = null): DOMDocument
    {
        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();
        return $doc;
    }
}
