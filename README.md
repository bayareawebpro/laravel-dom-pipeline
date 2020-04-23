# Laravel DOM Pipeline


![](https://github.com/bayareawebpro/laravel-dom-pipeline/workflows/ci/badge.svg)
![](https://img.shields.io/packagist/dt/bayareawebpro/laravel-dom-pipeline.svg)
![](https://img.shields.io/github/v/release/bayareawebpro/laravel-dom-pipeline.svg)
![](https://img.shields.io/badge/License-MIT-success.svg)


```shell script
composer require bayareawebpro/laravel-dom-pipeline
```

> https://packagist.org/packages/bayareawebpro/laravel-dom-pipeline

Laravel DOM Pipeline allows you to pipe HTML content through a series of classes 
which can be helpful with sanitization and server-side enhancement / modification of page 
elements. The pipeline will not return the `<body>` tag or any other tags outside of the 
body scope. 

> "libxml_use_internal_errors" is enabled allow any type of tag to operated on. 
See DomPipelineService::class (line 25).

## Usage: 
```php
use BayAreaWebPro\DomPipeline\DomPipeline;
use My\Pipes\{
    LazyLoadImageTags,
    LazyLoadVideoTags,
    BuildTableOfContents
};

$modified = DomPipeline::make($html, [
    LazyLoadImageTags::class,
    LazyLoadVideoTags::class,
    BuildTableOfContents::class,
]);
```

## Example Dom Pipe Class
```php
use DOMDocument;
use DOMXPath;
use Closure;

class UpdateHeaders{
    public function handle(DOMDocument $dom, Closure $next)
    {
        $xpath = new DOMXPath($dom);
        foreach ($xpath->query('//h1|//h2|//h3|//h4|//h5|//h6') as $node) {
            $node->nodeValue = "This is a {$node->tagName} tag.";
        }
        return $next($dom);
    }
}
```

### Replace Element with Fragment

```php
use DOMDocument;
use DOMXPath;
use Closure;

class ReplaceWithFragment{
    public function handle(DOMDocument $dom, Closure $next)
    {
        $fragment = $dom->createDocumentFragment();
        $fragment->appendXML(<<<HTML
        <div class="my-4 p-3 shadow rounded border border-green-500 text-center">
            My Special Element
        </div>
        HTML);
        $h1 = $dom->getElementsByTagName('h1')->item(0);
        $body = $dom->getElementsByTagName('body')->item(0);
        $body->replaceChild($fragment, $h1);
        return $next($dom);
    }
}
```
