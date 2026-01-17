<?php

require_once __DIR__ . '/../../DomQuery.php';
require_once __DIR__ . '/../../PageInfoLoader.php';


class SpiegelLoader implements PageInfoLoader
{
    private DOMDocument $dom;
    private DomQuery $domQuery;

    public function __construct(DOMDocument $dom)
    {
        $this->dom = $dom;
        $this->domQuery = new DomQuery($dom);
    }

    public function extractCreationDate(): ?string
    {
        $metaTags = $this->dom->getElementsByTagName('time');
        foreach ($metaTags as $meta) {
            if ($meta->getAttribute('datetime') != null) {
                $dt = $meta->getAttribute('datetime');
                $d = date_parse_from_format("Y-m-d H:i:s", $dt);
                return date('Y-m-d H:i:s', mktime($d['hour'], $d['minute'], $d['second'], $d['month'], $d['day'], $d['year']));
            }
        }
        return null;
    }

    public function extractTitle(): ?string
    {
        $titles = $this->domQuery->querySelector('.headline-title');
        if ($titles != null) {
            return trim($titles->textContent);
        }

        return null;
    }

    public function extractPageName(): string
    {
        return "Spiegel";
    }

    public function extractAuthor(): ?string
    {
        $author = $this->domQuery->querySelector('span.article-header-author');
        if ($author != null) {
            $auth = trim($author->textContent);
            $parts = explode(',', $auth);
            if (count($parts) == 1) {
                return trim($parts[0]);
            }
            return "" . trim($parts[1]) . " " . trim($parts[0]);
        }
        return "Unknown Author";
    }

    public function extractTrustLevelOutOf100(): int
    {
        return 90;
    }

    public static function isValidUrl(string $url, string $domainName): bool
    {
        if ($domainName !== "spiegel.de") {
            return false;
        }
        return true;
    }
}
