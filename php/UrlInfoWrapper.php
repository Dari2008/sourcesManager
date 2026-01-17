<?php

require_once __DIR__ . '/PageInfoLoader.php';
require_once __DIR__ . '/loaders/german/Tagesschau.php';
require_once __DIR__ . '/loaders/german/Bild.php';
require_once __DIR__ . '/loaders/german/ZDFHeute.php';
require_once __DIR__ . '/loaders/german/Deutschlandfunk.php';
require_once __DIR__ . '/loaders/german/Spiegel.php';
require_once __DIR__ . '/loaders/german/Sueddeutsche.php';
require_once __DIR__ . '/loaders/german/Zeit.php';
require_once __DIR__ . '/loaders/german/FAZ.php';
require_once __DIR__ . '/loaders/german/Handelsblatt.php';
require_once __DIR__ . '/loaders/german/TAZ.php';
require_once __DIR__ . '/loaders/german/Welt.php';
require_once __DIR__ . '/loaders/german/Focus.php';

require_once __DIR__ . '/loaders/international/Youtube.php';

require_once __DIR__ . '/loaders/DefaultParser.php';

$allParsers = [
    TagesschauLoader::class,
    BildLoader::class,
    YoutubeLoader::class,
    ZDFHeuteLoader::class,
    DeutschlandfunkLoader::class,
    SpiegelLoader::class,
    SueddeutscheLoader::class,
    ZeitLoader::class,
    FAZLoader::class,
    HandelsblattLoader::class,
    TAZLoader::class,
    WeltLoader::class,
    FocusLoader::class,

];

function loadDataFromUrl($url): PageInfoLoader
{
    global $allParsers;
    $urlInfo = parse_url($url, PHP_URL_HOST);
    $re = '/(([^.]*?)\.([^.]*?))$/m';
    preg_match_all($re, $urlInfo, $matches, PREG_SET_ORDER, 0);
    $domainName = $matches[0][0];

    $finalParser = null;

    foreach ($allParsers as $parserClass) {
        $isValid = $parserClass::isValidUrl($url, $domainName);
        if (!$isValid) {
            continue;
        } else {
            $finalParser = $parserClass;
            break;
        }
    }

    if ($finalParser === null) {
        $finalParser = DefaultLoader::class;
    }

    try {
        $html = file_get_contents($url);
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $loader = new $finalParser($dom, $url);
        return $loader;
    } catch (Throwable $e) {
    }

    return new DefaultLoader(new DOMDocument(), $url, $domainName);
}
