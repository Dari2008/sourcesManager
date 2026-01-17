<?php

require_once __DIR__ . '/../../DomQuery.php';
require_once __DIR__ . '/../../PageInfoLoader.php';


class ZDFHeuteLoader implements PageInfoLoader
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
        $titles = $this->dom->getElementsByTagName('title');
        if ($titles->length > 0) {
            return trim($titles->item(0)->textContent);
        }

        return null;
    }

    public function extractPageName(): string
    {
        return "ZDFHeute";
    }

    public function extractAuthor(): ?string
    {
        return "Unknown Author";
    }

    public function extractTrustLevelOutOf100(): int
    {
        return 94;
    }

    public static function isValidUrl(string $url, string $domainName): bool
    {
        if ($domainName !== "zdfheute.de") {
            return false;
        }
        return true;
    }
}
