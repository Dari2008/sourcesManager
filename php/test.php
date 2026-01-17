<?php

$url = 'https://www.reuters.com/world/americas/us-talks-with-hardline-venezuelan-minister-cabello-began-months-before-raid-2026-01-17/';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

echo htmlspecialchars($response);
