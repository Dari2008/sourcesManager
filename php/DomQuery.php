<?php

class DomQuery
{
    private DOMXPath $xpath;

    public function __construct(DOMDocument $dom)
    {
        $this->xpath = new DOMXPath($dom);
    }

    public function querySelector(string $selector, ?DOMElement $context = null): ?DOMElement
    {
        $nodes = $this->querySelectorAll($selector, $context);
        return $nodes->length ? $nodes->item(0) : null;
    }

    public function querySelectorAll(string $selector, ?DOMElement $context = null): DOMNodeList
    {
        $xpath = $this->cssToXpath($selector);

        return $this->xpath->query(
            $context ? ".{$xpath}" : $xpath,
            $context
        );
    }

    private function cssToXpath(string $selector): string
    {
        $parts = preg_split('/\s+/', trim($selector));
        $xpath = '';

        foreach ($parts as $part) {
            $xpath .= '//' . $this->simpleSelectorToXpath($part);
        }

        return $xpath;
    }

    private function simpleSelectorToXpath(string $selector): string
    {
        $tag = '*';
        $conditions = [];

        if (preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*/', $selector, $m)) {
            $tag = $m[0];
        }

        if (preg_match_all('/\.([a-zA-Z0-9_-]+)/', $selector, $m)) {
            foreach ($m[1] as $class) {
                $conditions[] =
                    "contains(concat(' ', normalize-space(@class), ' '), ' $class ')";
            }
        }

        if (preg_match('/#([a-zA-Z0-9_-]+)/', $selector, $m)) {
            $conditions[] = "@id='{$m[1]}'";
        }

        if (!$conditions) {
            return $tag;
        }

        return $tag . '[' . implode(' and ', $conditions) . ']';
    }
}
