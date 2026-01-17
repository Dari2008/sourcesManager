<?php

require_once __DIR__ . '/../../DomQuery.php';
require_once __DIR__ . '/../../PageInfoLoader.php';


class WeltLoader implements PageInfoLoader
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
                    $title = trim($meta->getAttribute('content'));
                    return str_replace(" - WELT", "", $title);
                }
            }
        }

        return null;
    }

    public function extractPageName(): string
    {
        return "Die Welt";
    }

    public function extractAuthor(): ?string
    {
        $titles = $this->domQuery->querySelector(".c-article-header__author-by .c-article-header__author-profile-link");
        if ($titles !== null) {
            return trim($titles->textContent);
        }
        return "Unknown Author";
    }

    public function extractTrustLevelOutOf100(): int
    {
        return 70;
    }

    public static function isValidUrl(string $url, string $domainName): bool
    {
        if ($domainName !== "welt.de") {
            return false;
        }
        return true;
    }
}
