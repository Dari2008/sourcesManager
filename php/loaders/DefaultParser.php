<?php

require_once __DIR__ . '/../DomQuery.php';
require_once __DIR__ . '/../PageInfoLoader.php';


class DefaultLoader implements PageInfoLoader
{
    private DOMDocument $dom;
    private DomQuery $domQuery;
    private string $url;
    private string $domainName;

    public function __construct(DOMDocument $dom, string $url, string $domainName)
    {
        $this->dom = $dom;
        $this->domQuery = new DomQuery($dom);
        $this->url = $url;
        $this->domainName = $domainName;
    }

    private function loadFromMeta(DOMNodeList $metaTags, string $tageName)
    {
        $metaTags = $this->dom->getElementsByTagName('meta');
        foreach ($metaTags as $meta) {
            if ($meta->getAttribute('property') === $tageName || $meta->getAttribute('name') === $tageName) {
                $dt = $meta->getAttribute('content');
                return date('Y-m-d H:i:s', strtotime($dt));
            }
        }
    }

    private function loadFromMetaTagWithAlternatives(DOMNodeList $metaTags, array $tageNames)
    {
        foreach ($tageNames as $tageName) {
            $result = $this->loadFromMeta($metaTags, $tageName);
            if ($result !== null) {
                return $result;
            }
        }
        return null;
    }

    public function extractCreationDate(): ?string
    {
        $metaTags =  $this->dom->getElementsByTagName('meta');
        $articleCreationDate = $this->loadFromMetaTagWithAlternatives($metaTags, [
            "article:published_time",
            "og:published_time",
            "og:article:published_time",
            "published_time",
            "publication_date",
            "og:publication_date",
        ]);
        if ($articleCreationDate !== null) {
            return date('Y-m-d H:i:s', strtotime($articleCreationDate));
        }

        $timeTags = $this->dom->getElementsByTagName('time');
        foreach ($timeTags as $meta) {
            if ($meta->getAttribute('datetime') != null) {
                $dt = $meta->getAttribute('datetime');
                return date('Y-m-d H:i:s', strtotime($dt));
            }
        }
        return null;
    }

    public function extractTitle(): ?string
    {
        $titles = $this->dom->getElementsByTagName("meta");
        $title = $this->loadFromMetaTagWithAlternatives($titles, [
            "og:title",
            "twitter:title",
            "title",
            "og:article:title"
        ]);
        if ($title !== null) {
            return trim($title);
        }

        return null;
    }

    public function extractPageName(): string
    {
        $metaTags = $this->dom->getElementsByTagName('meta');
        $pageName = $this->loadFromMetaTagWithAlternatives($metaTags, [
            "og:site_name",
            "application-name",
            "apple-mobile-web-app-title",
            "site_name"
        ]);
        if ($pageName !== null) {
            return trim($pageName);
        }

        $domainName = parse_url($this->url, PHP_URL_HOST);
        $re = '/([^.]*?)\.[^.]*?$/m';
        preg_match_all($re, $domainName, $matches, PREG_SET_ORDER, 0);
        $domainName = $matches[0][1];
        $domainName[0] = strtoupper($domainName[0]);
        return $domainName;
    }

    public function extractAuthor(): ?string
    {
        $metaTags = $this->dom->getElementsByTagName("meta");
        $author = $this->loadFromMetaTagWithAlternatives($metaTags, [
            "article:author",
            "og:article:author",
            "author",
            "article:creator",
            "og:creator"
        ]);
        if ($author !== null) {
            return trim($author);
        }
        return "Unknown Author";
    }

    public function extractTrustLevelOutOf100(): int | string
    {

        $rankingJson = file_get_contents(__DIR__ . '/trustLevels.json');
        $rankingData = json_decode($rankingJson, true);
        if (isset($rankingData[$this->domainName])) {
            return $rankingData[$this->domainName];
        }

        return "Unknown Trust Level";
    }

    public static function isValidUrl(string $url, string $domainName): bool
    {
        if ($domainName !== "reuters.com") {
            return false;
        }
        return true;
    }
}
