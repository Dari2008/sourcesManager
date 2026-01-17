<?php

require_once __DIR__ . '/../../PageInfoLoader.php';
require_once __DIR__ . '/../../DomQuery.php';



class BildLoader implements PageInfoLoader
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
        $dateTimeElement = $this->domQuery->querySelector('.datetime--article.datetime');
        if ($dateTimeElement !== null) {
            $dt = trim($dateTimeElement->textContent);
            $dateTime = date_parse_from_format("d.m.Y - H:i \\U\\h\\r", $dt);
            return date("Y-m-d H:i:s", mktime($dateTime['hour'], $dateTime['minute'], $dateTime['second'], $dateTime['month'], $dateTime['day'], $dateTime['year']));
        }
        return "No Creation Date Found";
    }

    public function extractTitle(): ?string
    {
        $head = $this->domQuery->querySelector('.headline');
        if ($head !== null) {
            return trim($head->textContent);
        }
        $titleTag = $this->dom->getElementsByTagName('title')->item(0);
        if ($titleTag !== null) {
            return trim($titleTag->textContent);
        }
        return "No Title Found";
    }

    public function extractPageName(): string
    {
        return "Bild";
    }

    public function extractAuthor(): ?string
    {
        $authors = $this->domQuery->querySelectorAll('.article_author .article_author__details');
        if (count($authors) > 0) {
            $authorNames = [];
            foreach ($authors as $authorElement) {
                $p1 = $authorElement->getElementsByTagName('p')->item(0);
                $p2 = $authorElement->getElementsByTagName('p')->item(1);
                if ($p1 == null) $p1 = "";
                if ($p2 == null) $p2 = "";
                if (str_contains($p2->getAttribute("class"), "article_author__location")) $p2 = "";
                $p1 = preg_replace('/\((.*?)\)/', '', is_string($p1) ? $p1 : $p1->textContent);
                $p2 = preg_replace('/\((.*?)\)/', '', is_string($p2) ? $p2 : $p2->textContent);
                $authorNames[] = trim($p1 . ' ' . $p2);
            }
            return implode(', ', $authorNames);
        }
        return "No Author Found";
    }

    public function extractTrustLevelOutOf100(): int
    {
        return 40;
    }

    public static function isValidUrl(string $url, string $domainName): bool
    {
        return str_contains($domainName, 'bild.de');
    }
}
