<?php declare(strict_types=1);

namespace BayAreaWebPro\DomPipeline\Tests\Unit;

use BayAreaWebPro\DomPipeline\Tests\TestCase;
use BayAreaWebPro\DomPipeline\DomPipeline;
use DOMDocument;
use DOMXPath;

class DefaultTest extends TestCase
{
    public function test_can_pipe_dom_and_only_return_body_children()
    {
        $html = <<<HTML
        <h1>Test</h1>
        <h2>Test</h2>
        <h3>Test</h3>
        <h4>Test</h4>
        <h5>Test</h5>
        <h6>Test</h6>
        HTML;

        $actual = DomPipeline::make($html, [
            new class{
                public function handle(DOMDocument $dom, \Closure $next){
                    $xpath = new DOMXPath($dom);
                    foreach ($xpath->query('//h1|//h2|//h3|//h4|//h5|//h6') as $node) {
                        $node->nodeValue = $node->tagName;
                    }
                    return $next($dom);
                }
            },
            new class{
                public function handle(DOMDocument $dom, \Closure $next){
                    $xpath = new DOMXPath($dom);
                    foreach ($xpath->query('//h1|//h2|//h3|//h4|//h5|//h6') as $node) {
                        $node->setAttribute("class", "edited");
                    }
                    return $next($dom);
                }
            }
        ]);

        $expected = <<<HTML
        <h1 class="edited">h1</h1>
        <h2 class="edited">h2</h2>
        <h3 class="edited">h3</h3>
        <h4 class="edited">h4</h4>
        <h5 class="edited">h5</h5>
        <h6 class="edited">h6</h6>
        HTML;

        $this->assertSame($expected, $actual);
    }

    public function test_can_pipe_null()
    {
        $html = null;
        $actual = DomPipeline::make($html, [
            new class{
                public function handle(DOMDocument $dom, \Closure $next){
                    $xpath = new DOMXPath($dom);
                    foreach ($xpath->query('//h1|//h2|//h3|//h4|//h5|//h6') as $node) {
                        $node->nodeValue = $node->tagName;
                    }
                    return $next($dom);
                }
            }
        ]);
        $this->assertSame($html, $actual);
    }
}
