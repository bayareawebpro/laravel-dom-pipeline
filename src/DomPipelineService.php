<?php declare(strict_types=1);

namespace BayAreaWebPro\DomPipeline;

use DOMDocument;
use Illuminate\Pipeline\Pipeline;

class DomPipelineService
{
    /**
     * The pipeline instance.
     * @var Pipeline
     */
    protected Pipeline $pipeline;

    /**
     * The array of pipes to be processed.
     * @var array
     */
    protected array $pipes;

    /**
     * Make Pipeline instance & process it.
     * @param string|null $html
     * @param array $pipes
     * @return mixed
     */
    public static function make(?string $html = null, array $pipes = [])
    {
        return app(static::class, compact('pipes'))->process($html);
    }

    /**
     * DomPipelineService constructor.
     * @param Pipeline $pipeline
     * @param array $pipes
     */
    public function __construct(Pipeline $pipeline, array $pipes = [])
    {
        $this->pipeline = $pipeline;
        $this->pipes = $pipes;
    }

    /**
     * Process Pipes
     * @param string|null $html
     * @return string|null
     */
    protected function process(?string $html = null)
    {
        if (empty($html)) return $html;

        return (string)$this->pipeline
            ->send($this->getDom($html))
            ->through($this->pipes)
            ->via('handle')
            ->then(function (DOMDocument $dom) {
                return $this->getHtml($dom);
            });
    }

    /**
     * Get Html Content
     * @param DOMDocument $dom
     * @return string
     */
    protected function getHtml(DOMDocument $dom): string
    {
        $html = $dom->saveHTML($dom->getElementsByTagName('body')->item(0));
        return str_replace(['<body>', '</body>'], '', $html);
    }

    /**
     * Get DOMDocument Instance with specified HTML content.
     * @param string $html
     * @return DOMDocument
     */
    protected static function getDom(string $html): DOMDocument
    {
        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();
        return $doc;
    }
}
