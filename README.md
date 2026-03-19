# Laravel DOM Pipeline

![](https://github.com/bayareawebpro/laravel-dom-pipeline/workflows/ci/badge.svg)
![](https://codecov.io/gh/bayareawebpro/laravel-dom-pipeline/branch/master/graph/badge.svg)
![](https://img.shields.io/github/v/release/bayareawebpro/laravel-dom-pipeline.svg)
![](https://img.shields.io/packagist/dt/bayareawebpro/laravel-dom-pipeline.svg)
![](https://img.shields.io/badge/License-MIT-success.svg)

```shell script
composer require bayareawebpro/laravel-dom-pipeline
```

> https://packagist.org/packages/bayareawebpro/laravel-dom-pipeline

Laravel DOM Pipeline allows you to pipe HTML content through a series of classes 
which can be helpful with sanitization and server-side enhancement / modification of page 
elements. The pipeline will not return the `<body>` tag or any other tags outside the 
body scope. 

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

### Example Dom Pipe Class

> Docs: https://www.php.net/manual/en/book.dom.php

```php
use DOMDocument;
use DOMXPath;
use Closure;

class UpdateHeaders{
    public function handle(DOMDocument $dom, Closure $next)
    {
        $xpath = new DOMXPath($dom);
        foreach ($xpath->query('//h1|//h2|//h3|//h4|//h5|//h6') as $node) {
            // Change the header tags content.
            $node->nodeValue = "This is a {$node->tagName} tag.";
        }
        return $next($dom);
    }
}
```

### LazyLoad Images
```php
<?php declare(strict_types=1);

namespace App\Services\Html\Formatters;

use Closure;
use DOMElement;
use DOMDocument;

class LazyLoadImages
{
    /**
     * @param DOMDocument $dom
     * @param Closure $next
     * @return mixed
     */
    public function handle(DOMDocument $dom, Closure $next)
    {
        foreach ($dom->getElementsByTagName('img') as $node) {
            $this->lazyLoad($node);
        }
        return $next($dom);
    }

    /**
     * @param DOMDocument $dom
     * @param DOMElement $node
     */
    protected function lazyLoad(DOMElement $node): void
    {
        if (!$node->hasAttribute('data-src')) {
            // Set the data-src attribute.
            $node->setAttribute('data-src', $node->getAttribute('src'));

            // Set the src attribute to loading image.
            $node->setAttribute('src', asset('images/loading.gif'));

            // Merge the lazy load class into the class list.
            $node->setAttribute('class',join(' ', [
                $node->getAttribute('class'),
                'lazy-load'
            ]));
        }
    }
}
```

### Element to VueComponent

Convert an Iframe into a Vue Component extracting the video ID from the URL.

```php
<?php declare(strict_types=1);

namespace App\Services\Html\Formatters;

use Closure;
use DOMElement;
use DOMDocument;

class LazyLoadVideos
{
    /**
     * @param DOMDocument $dom
     * @param Closure $next
     * @return mixed
     */
    public function handle(DOMDocument $dom, Closure $next)
    {
        $xpath = new \DOMXPath($dom);
        foreach($xpath->query('//iframe[@class="media"]') as $node){
            $this->lazyLoad($dom, $node);
        }
        return $next($dom);
    }

    /**
     * @param DOMDocument $dom
     * @param DOMElement $node
     */
    protected function lazyLoad(DOMDocument $dom, DOMElement $node): void
    {
        if(is_null($node->parentNode)) return;

        // Match the YouTube Video ID.
        // https://stackoverflow.com/questions/2936467/parse-youtube-video-id-using-preg-match
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i',
            (string) $node->getAttribute('src'), $matches
        );

        if(isset($matches[1])){
            // Create a new HTML fragment.
            $fragment = $dom->createDocumentFragment();
            $fragment->appendXML(<<<HTML
            <v-video id="$matches[1]" label="Click to play..." :show-image="true"></v-video>
            HTML);

            // Replace Self with Fragment.
            $node->parentNode->replaceChild($fragment, $node);
        }
    }
}
```


### Table of Contents

```php
<?php declare(strict_types=1);

namespace App\Services\Html\Formatters;

use DOMElement;
use Closure;
use StdClass;
use DOMDocument;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;

class TableOfContents
{
    /**
     * @param DOMDocument $dom
     * @param Closure $next
     * @return mixed
     */
    public function handle(DOMDocument $dom, Closure $next)
    {
        $nodes = Collection::make();
        $xpath = new \DOMXPath($dom);

        foreach ($xpath->query('//h1|//h2|//h3|//h4|//h5|//h6') as $node) {
            $text = strip_tags(html_entity_decode((string) $node->nodeValue));
            $nodes->push($this->makeBookmark($node, $text));
        }

        if($nodes->count() > 5){
            View::share('tableOfContents', $nodes->take(25));
        }

        return $next($dom);
    }

    /**
     * @param DOMElement $node
     * @param string $text
     * @return StdClass
     */
    protected function makeBookmark(DOMElement $node, string $text): StdClass
    {
        // Create the bookmark item.
        $bookmark = (object)[
          'anchor' => Str::slug($text),
          'text'   => Str::title(Str::replaceLast('.', '', $text)),
        ];

        // Link the bookmark to the header using the ID.
        $node->setAttribute('id', $bookmark->anchor);

        return $bookmark;
    }
}
```
