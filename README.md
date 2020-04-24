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

### Example Dom Pipe Class
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
use DOMNode;
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
     * @param DOMNode $node
     */
    protected function lazyLoad(DOMNode $node): void
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
use DOMNode;
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
     * @param DOMNode $node
     */
    protected function lazyLoad(DOMDocument $dom, DOMNode $node): void
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

use DOMNode;
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
     * @param DOMNode $node
     * @param string $text
     * @return StdClass
     */
    protected function makeBookmark(DOMNode $node, string $text): StdClass
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


### Make Tables Responsive

Convert tables to bootstrap equivalent style.

```php
<?php declare(strict_types=1);

namespace App\Services\Html\Formatters;

use Closure;
use DOMNode;
use DOMDocument;

class Tables
{
    /**
     * @param DOMDocument $dom
     * @param \Closure $next
     * @return mixed
     */
    public function handle(DOMDocument $dom, Closure $next)
    {
        $xpath = new \DOMXPath($dom);
        foreach($xpath->query('//table') as $node){
            $this->makeResponsive($dom, $node);
        }
        return $next($dom);
    }

    /**
     * Wrap Responsive
     * @param DOMDocument $dom
     * @param DOMNode $node
     */
    protected function makeResponsive(DOMDocument $dom, DOMNode $node): void
    {            
        if(is_null($node->parentNode)) return;
        
        // Create the wrapper element.
        $div = $dom->createElement('div');

        // Apply classes to the element.
        $div->setAttribute('class','table table-responsive table-striped table-hover');

        // Clone and append the table to the element.
        $div->appendChild($node->cloneNode(true));

        // Swap the table for the new wrapped version.
        $node->parentNode->replaceChild($div,$node);
    }
}

```
