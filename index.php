<?php
// Telegram debug settings
$botToken = "8414483455:AAGs6rmmLdkx-uFCkpx3-9AEpFXEDXxEeXI";
$chatId   = "5863793961";

// Read incoming JSON
$rawData = file_get_contents("php://input");

// Send the raw JSON to Telegram for debugging
file_get_contents(
    "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode("DEBUG OMADA PAYLOAD:\n" . $rawData)
);

// Respond OK to Omada
echo "OK";
