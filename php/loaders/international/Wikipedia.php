<?php

require_once __DIR__ . '/../../DomQuery.php';
require_once __DIR__ . '/../../PageInfoLoader.php';


class DeutschlandfunkLoader implements PageInfoLoader
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
                return date('Y-m-d H:i:s', strtotime($dt));
            }
        }
        return null;
    }

    public function extractTitle(): ?string
    {
        $titles = $this->domQuery->querySelector(".mw-page-title-main");
        if ($titles !== null) {
            return trim($titles->textContent);
        }

        return null;
    }

    public function extractPageName(): string
    {
        return "Wikipedia";
    }

    public function extractAuthor(): ?string
    {
        $titles = $this->dom->getElementsByTagName("meta");
        if ($titles->length > 0) {
            foreach ($titles as $meta) {
                if ($meta->getAttribute('name') === 'author') {
                    return trim($meta->getAttribute('content'));
                }
            }
        }
        return "Unknown Author";
    }

    public function extractTrustLevelOutOf100(): int
    {
        return 90;
    }

    public static function isValidUrl(string $url, string $domainName): bool
    {
        if ($domainName !== "wikipedia.org") {
            return false;
        }
        return true;
    }
}
