<?php

require_once __DIR__ . '/../../DomQuery.php';
require_once __DIR__ . '/../../PageInfoLoader.php';


class ZeitLoader implements PageInfoLoader
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
        $metaTags = $this->dom->getElementsByTagName('meta');
        foreach ($metaTags as $meta) {
            if ($meta->getAttribute('name') == "date") {
                $dt = $meta->getAttribute('content');
                return date('Y-m-d H:i:s', strtotime($dt));
            }
        }
        return null;
    }

    public function extractTitle(): ?string
    {
        $titles = $this->dom->getElementsByTagName("meta");
        if ($titles->length > 0) {
            foreach ($titles as $meta) {
                if ($meta->getAttribute('property') === 'og:title') {
                    return trim($meta->getAttribute('content'));
                }
            }
        }

        return null;
    }

    public function extractPageName(): string
    {
        return "Zeit";
    }

    public function extractAuthor(): ?string
    {
        $titles = $this->dom->getElementsByTagName("a");
        if ($titles->length > 0) {
            foreach ($titles as $meta) {
                if ($meta->getAttribute('rel') === 'author') {
                    return trim($meta->textContent);
                }
            }
        }
        return "Unknown Author";
    }

    public function extractTrustLevelOutOf100(): int
    {
        return 92;
    }

    public static function isValidUrl(string $url, string $domainName): bool
    {
        if ($domainName !== "zeit.de") {
            return false;
        }
        return true;
    }
}
