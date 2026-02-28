<?php

$botToken = "8414483455:AAGs6rmmLdkx-uFCkpx3-9AEpFXEDXxEeXI";
$chatId   = "5863793961";

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$eventType = $data['eventType'] ?? 'Unknown Event';
$deviceName = $data['deviceName'] ?? 'Unknown Device';
$siteName = $data['siteName'] ?? 'Default';

$message  = "📡 OMADA ALERT\n\n";
$message .= "Event: $eventType\n";
$message .= "Device: $deviceName\n";
$message .= "Site: $siteName\n";
$message .= "Time: " . date("Y-m-d H:i:s");

$url = "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($message);

file_get_contents($url);

echo "OK";