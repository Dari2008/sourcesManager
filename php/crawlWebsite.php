<?php

header('Content-Type: application/json; charset=utf-8');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
} else if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/UrlInfoWrapper.php';

// $websiteURL = "https://www.tagesschau.de/ausland/asien/iran-proteste-290.html";
// $websiteURL = "https://www.tagesschau.de/wirtschaft/weltwirtschaft/unterzeichnung-mercosur-abkommen-102.html";
// $websiteURL = "https://www.tagesschau.de/ausland/amerika/mercosur-argentinien-autozulieferer-100.html";
// $websiteURL = "https://www.tagesschau.de/ausland/amerika/usa-ice-befugnisse-urteil-100.html";
// $websiteURL = "https://www.bild.de/news/ausland/protokolle-aus-todes-bar-meine-freundin-sagte-lass-mich-nicht-allein-696a59796b40895ba7f702f4";
// $websiteURL = "https://www.bild.de/politik/ausland-und-internationales/groenland-krise-daenemark-bestaetigt-koeniglichen-einsatzbefehl-696adffe6b40895ba7f709d3";
// $websiteURL = "https://www.youtube.com/watch?v=_VwDMJG4vYk";
// $websiteURL = "https://www.zdfheute.de/politik/ausland/iran-usa-sanktionen-militaerschlag-jan-busse-100.html";
// $websiteURL = "https://www.deutschlandfunk.de/usa-groenland-ukraine-winter-100.html";
// $websiteURL = "https://www.spiegel.de/ausland/minnesota-ice-agenten-essen-erst-beim-mexikaner-nehmen-danach-offenbar-die-mitarbeiter-mit-a-b355371f-edd9-441f-80b0-2738d2f2f894";
// $websiteURL = "https://www.sueddeutsche.de/panorama/leonardo-dicaprio-memes-golden-globes-mimik-li.3369788?reduced=true";
// $websiteURL = "https://www.zeit.de/kultur/2026-01/lage-iran-angst-sorge-familie-freunde";
// $websiteURL = "https://www.zeit.de/politik/ausland/2026-01/gaza-friedensrat-mitglieder-donald-trump-friedensplan";
// $websiteURL = "https://www.faz.net/aktuell/politik/ausland/neue-weltordnung-es-lohnt-sich-fuer-die-nato-zu-kaempfen-accg-110820422.html";
// $websiteURL = "https://www.handelsblatt.com/politik/international/auszeichnung-karlspreis-fuer-mario-draghi-grosses-fuer-europa-geleistet/100192414.html";
// $websiteURL = "https://taz.de/Ein-optimistischer-Ausblick-auf-das-Jahr/!6142247/";
// $websiteURL = "https://www.welt.de/finanzen/plus696a3ce404401bff3baa52c5/shanghai-102-new-york-90-dollar-das-steckt-hinter-der-seltsamen-silber-luecke.html";
// $websiteURL = "https://www.focus.de/politik/deutschland/chef-der-bayerischen-gruenen-jugend-beleidigt-markus-soeder-als-hurensohn_41e82384-38ed-483d-9f9a-80bbdc9f6c23.html";
// $websiteURL = "https://www.reuters.com/world/americas/us-talks-with-hardline-venezuelan-minister-cabello-began-months-before-raid-2026-01-17/";

$dataRaw = file_get_contents('php://input');
$data = json_decode($dataRaw, true);

$websiteURL = $data['websiteURL'];

if (!filter_var($websiteURL, FILTER_VALIDATE_URL)) {
    echo json_encode([
        "status" => "error",
        "error" => "Invalid URL"
    ]);
    exit;
}


$loader = loadDataFromUrl($websiteURL);

$title = $loader->extractTitle();
$creationDate = $loader->extractCreationDate();
$author = $loader->extractAuthor();
$pageName = $loader->extractPageName();
$trustLevel = $loader->extractTrustLevelOutOf100();

echo json_encode([
    "status" => "success",
    "data" => [
        "title" => $title,
        "creationDate" => $creationDate,
        "author" => $author,
        "pageName" => $pageName,
        "trustLevelOutOf100" => $trustLevel
    ]
]);

// $domElement = new DOMDocument();
// @$domElement->loadHTML($websiteContent);
// $tagesschauLoader = new TagesschauLoader($domElement);
// $title = $tagesschauLoader->extractTitle();
// $creationDate = $tagesschauLoader->extractCreationDate();
// $author = $tagesschauLoader->extractAuthor();
// echo "Title: " . $title . PHP_EOL . "<br>";
// echo "Creation Date: " . $creationDate . PHP_EOL . "<br>";
// echo "Author: " . $author . PHP_EOL . "<br>";
