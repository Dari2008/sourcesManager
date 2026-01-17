<?php

require_once __DIR__ . '/../../DomQuery.php';
require_once __DIR__ . '/../../PageInfoLoader.php';


class FAZLoader implements PageInfoLoader
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
        $titles = $this->dom->getElementsByTagName("div");
        if ($titles->length > 0) {
            foreach ($titles as $meta) {
                if ($meta->getAttribute('data-external-selector') === 'header-title') {
                    return trim($meta->textContent);
                }
            }
        }

        return null;
    }

    public function extractPageName(): string
    {
        return "Frankfurter Allgemeine Zeitung";
    }

    public function extractAuthor(): ?string
    {
        $titles = $this->dom->getElementsByTagName("a");
        if ($titles->length > 0) {
            foreach ($titles as $meta) {
                if ($meta->getAttribute('rel') === 'author') {
                    $auth = trim($meta->textContent);
                    return preg_replace('/, (.*?)$/', '', $auth);
                }
            }
        }
        return "Unknown Author";
    }

    public function extractTrustLevelOutOf100(): int
    {
        return 88;
    }

    public static function isValidUrl(string $url, string $domainName): bool
    {
        if ($domainName !== "faz.net") {
            return false;
        }
        return true;
    }
}
