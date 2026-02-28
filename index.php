<?php
$botToken = "8414483455:AAGs6rmmLdkx-uFCkpx3-9AEpFXEDXxEeXI";
$chatId   = "5863793961";

// Capture raw webhook payload
$rawData = file_get_contents("php://input");

// Send payload to Telegram
file_get_contents(
    "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode("DEBUG OMADA PAYLOAD:\n" . $rawData)
);

// Always respond OK
echo "OK";
