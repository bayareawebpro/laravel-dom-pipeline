<?php declare(strict_types=1);

namespace BayAreaWebPro\DomPipeline;

use DOMDocument;
use Illuminate\Support\Str;

class DomParser
{
    /**
     * "mb_convert_encoding" is deprecated.
     * @example https://stackoverflow.com/questions/8218230/php-domdocument-loadhtml-not-encoding-utf-8-correctly/37834812#37834812
     */
    public static function make(string $html): \DOMDocument
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML(<<<HTML
        <!DOCTYPE html>
            <html lang="en">
            <head>
                <title>Document</title>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            </head>
            <body>$html</body>
        </html>
        HTML
        );
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        return $doc;
    }

    public static function getBodyHtml(DOMDocument $dom): string
    {
        $html = $dom->saveHTML($dom->getElementsByTagName('body')->item(0));

        return Str::remove(['<body>', '</body>'], $html);
    }
}
