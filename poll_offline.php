<?php
// poll_offline.php

// Set your bot token and chat ID
$botToken = 'YOUR_BOT_TOKEN';
$chatId = 'YOUR_CHAT_ID';

// URL for webhook
$url = "https://api.telegram.org/bot$botToken/sendMessage";

// Polling loop
while (true) {
    // Retrieve data from Telegram
    $update = file_get_contents("https://api.telegram.org/bot$botToken/getUpdates");
    $updateArray = json_decode($update, true);

    // Check if we have received any messages
    if (!empty($updateArray["result"])) {
        foreach ($updateArray["result"] as $message) {
            // Process the message here
            $text = $message["message"]["text"];
            // Send a response back
            $params = [
                'chat_id' => $chatId,
                'text' => "You said: " . $text
            ];
            file_get_contents($url . '?' . http_build_query($params));
        }
    }
    // Wait before polling again
    sleep(1);
}