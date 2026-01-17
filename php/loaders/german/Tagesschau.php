<?php

require_once __DIR__ . '/../../DomQuery.php';
require_once __DIR__ . '/../../PageInfoLoader.php';


class TagesschauLoader implements PageInfoLoader
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
            if ($meta->getAttribute('name') === 'date') {
                $dt = $meta->getAttribute('content');
                return strtotime($dt) ? date('Y-m-d H:i:s', strtotime($dt)) : null;
            }
        }
        return null;
    }

    public function extractTitle(): ?string
    {
        $headlines = $this->domQuery->querySelectorAll('.article-head__headline--text');
        if ($headlines->length > 0) {
            return trim($headlines->item(0)->textContent);
        }

        $titles = $this->dom->getElementsByTagName('title');
        if ($titles->length > 0) {
            return trim($titles->item(0)->textContent);
        }

        return null;
    }

    public function extractPageName(): string
    {
        return "Tagesschau";
    }

    public function extractAuthor(): ?string
    {

        $possibleAuthorLine = $this->domQuery->querySelector('.authorline__author .authorline__link');
        if ($possibleAuthorLine !== null) {
            return trim($possibleAuthorLine->textContent);
        }

        $possibleOnlyAuthorLine = $this->domQuery->querySelector('.authorline__author');
        if ($possibleOnlyAuthorLine !== null) {
            $parts = explode(" ", trim($possibleOnlyAuthorLine->textContent));
            if (count($parts) > 2) {
                $firstName = $parts[1];
                $lastName  = $parts[2];
                return str_replace(",", "", trim("$firstName $lastName"));
            } else if (count($parts) === 2) {
                $firstName = $parts[0];
                $lastName  = $parts[1];
                return str_replace(",", "", trim("$firstName $lastName"));
            }
        }

        $possibleInfos = $this->domQuery->querySelectorAll('.columns');

        foreach ($possibleInfos as $info) {
            $headlines = $this->domQuery->querySelectorAll('.infobox__headline', $info);

            if (
                $headlines->length === 1 &&
                trim($headlines->item(0)->textContent) === "Zur Person"
            ) {
                if ($info instanceof DOMElement) {
                    $paragraph = $info->textContent;

                    if (preg_match('/Zur Person\s*(.+)/s', $paragraph, $matches)) {
                        $text = trim(
                            str_replace("Zur Person", "", $paragraph)
                        );

                        $parts = preg_split('/\s+/', $text);
                        $firstName = $parts[0] ?? '';
                        $lastName  = $parts[1] ?? '';

                        return trim("$firstName $lastName");
                    }
                }
            }
        }
        $fullContent = $this->domQuery->querySelector("#content");
        if ($fullContent !== null) {
            $text = $fullContent->textContent;
            if (preg_match('/Von\s+([A-ZÄÖÜ][a-zäöüß]+(?:\s+[A-ZÄÖÜ][a-zäöüß]+)+)/', $text, $matches)) {
                return trim($matches[1]);
            }

            if (preg_match('/Nach Angaben von\s+([A-ZÄÖÜ][a-zäöüß]+(?:\s+[A-ZÄÖÜ][a-zäöüß]+)+)/', $text, $matches)) {
                return trim($matches[1]);
            }

            if (preg_match('/Mit Informationen von \s+([A-ZÄÖÜ][a-zäöüß]+(?:\s+[A-ZÄÖÜ][a-zäöüß]+)+)/', $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return "Tagesschau";
    }

    public function extractTrustLevelOutOf100(): int
    {
        return 90;
    }

    public static function isValidUrl(string $url, string $domainName): bool
    {
        if ($domainName !== "tagesschau.de") {
            return false;
        }
        return true;
    }
}
