<?php

require_once __DIR__ . '/../../DomQuery.php';
require_once __DIR__ . '/../../PageInfoLoader.php';


class FocusLoader implements PageInfoLoader
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
        return "Focus";
    }

    public function extractAuthor(): ?string
    {
        $titles = $this->domQuery->querySelectorAll("span.Content-Author-Text");
        if (count($titles) > 0) {
            return trim($titles[0]->textContent);
        }
        return "Unknown Author";
    }

    public function extractTrustLevelOutOf100(): int
    {
        return 65;
    }

    public static function isValidUrl(string $url, string $domainName): bool
    {
        if ($domainName !== "focus.de") {
            return false;
        }
        return true;
    }
}
