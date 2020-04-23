# Laravel DOM Pipeline

![](https://github.com/bayareawebpro/laravel-dom-pipeline/workflows/ci/badge.svg)
![](https://img.shields.io/packagist/dt/bayareawebpro/laravel-dom-pipeline.svg)
![](https://img.shields.io/github/v/release/bayareawebpro/laravel-dom-pipeline.svg)
![](https://img.shields.io/badge/License-MIT-success.svg)

> https://packagist.org/packages/bayareawebpro/laravel-dom-pipeline

## Usage: 
```php
$modified = DomPipeline::make($html, [
    LazyLoadImage::class,
    LazyLoadVideos::class,
    TableOfContents::class,
]);
```

## Example Dom Pipe Class
```php
use DOMDocument;
use DOMXPath;
use Closure;

class UpdateHeaders{
    public function handle(DOMDocument $dom, Closure $next){
        $xpath = new DOMXPath($dom);
        foreach ($xpath->query('//h1|//h2|//h3|//h4|//h5|//h6') as $node) {
            $node->nodeValue = $node->tagName;
        }
        return $next($dom);
    }
}
```

