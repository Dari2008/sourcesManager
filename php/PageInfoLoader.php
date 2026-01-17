<?php

interface PageInfoLoader
{
    public function extractCreationDate(): ?string;
    public function extractTitle(): ?string;
    public function extractPageName(): string;
    public function extractAuthor(): ?string;
    public function extractTrustLevelOutOf100(): int | string;
    public static function isValidUrl(string $url, string $domainName): bool;
}
