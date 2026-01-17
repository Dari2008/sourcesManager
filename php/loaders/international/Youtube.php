<?php

require_once __DIR__ . '/../../PageInfoLoader.php';
require_once __DIR__ . '/../../DomQuery.php';

class YoutubeApiWrapper
{

    private static string $ROOT_URL = "https://www.googleapis.com/youtube/v3";
    private static string $API_KEY = "{YOUR_YOUTUBE_API_KEY_HERE}";

    public static function getVideoDetailsById(string $videoId): ?array
    {
        $url = self::$ROOT_URL . "/videos?part=snippet&id=" . $videoId . "&key=" . self::$API_KEY;
        $json = file_get_contents($url);
        $data = json_decode($json, true);
        if (isset($data['items']) && count($data['items']) > 0) {
            return $data['items'][0]['snippet'];
        }
        return null;
    }
}


class YoutubeLoader implements PageInfoLoader
{

    private DOMDocument $dom;
    private DomQuery $domQuery;
    private $ytData = null;

    public function __construct(DOMDocument $dom, string $url)
    {
        $this->dom = $dom;
        $this->domQuery = new DomQuery($dom);
        $urlParts = parse_url($url);
        parse_str($urlParts['query'] ?? '', $queryParams);
        $videoId = $queryParams['v'] ?? null;
        if ($videoId !== null) {
            $this->ytData = YoutubeApiWrapper::getVideoDetailsById($videoId);
        }
    }

    public function extractCreationDate(): ?string
    {
        if ($this->ytData !== null && isset($this->ytData['publishedAt'])) {
            $dt = $this->ytData['publishedAt'];
            $timestamp = strtotime($dt);
            if ($timestamp !== false) {
                return date('Y-m-d H:i:s', $timestamp);
            }
        }
        // $dateTimeElement = $this->domQuery->querySelectorAll('.style-scope.yt-formatted-string');
        // echo json_encode($dateTimeElement);
        // $filtered = array_filter(
        //     iterator_to_array($dateTimeElement),
        //     fn($el) => str_contains($el->textContent, 'Premiered') || str_contains($el->textContent, 'Published on')
        // );

        // if (count($filtered) > 0) {
        //     $dt = trim(array_values($filtered)[0]->textContent);
        //     $dt = str_replace(['Published on ', 'Premiered '], '', $dt);
        //     $timestamp = strtotime($dt);
        //     if ($timestamp !== false) {
        //         return date('Y-m-d H:i:s', $timestamp);
        //     }
        // }
        return "No Creation Date Found";
    }

    public function extractTitle(): ?string
    {
        if ($this->ytData !== null && isset($this->ytData['title'])) {
            return $this->ytData['title'];
        }
        return "No Title Found";
    }

    public function extractPageName(): string
    {
        return "Youtube";
    }

    public function extractAuthor(): ?string
    {
        if ($this->ytData !== null && isset($this->ytData['channelTitle'])) {
            return $this->ytData['channelTitle'];
        }
        return "No Author Found";
    }

    public function extractTrustLevelOutOf100(): int
    {
        return 40;
    }

    public static function isValidUrl(string $url, string $domainName): bool
    {
        return str_contains($domainName, 'youtube.com') || str_contains($domainName, 'youtu.be') || str_contains($domainName, 'youtube.de');
    }
}
