<?php
// config.php — webhook configuration
$webhooks = [
    [
        'name' => 'Omada Webhook',
        'type' => 'omada', // Omada JSON payload (can be your own endpoint)
        'url' => 'https://omadawebhook.gamer.gd/index.php',
        'payload_template' => 'omada', 
        'webhook_id' => 'WH-001',
        'shared_secret' => 'shardSecret123',
        'retry_policy' => 3,
        'action' => 'active'
    ]
];

// Telegram bot config (used directly in index.php)
$telegramBotToken = "8414483455:AAGs6rmmLdkx-uFCkpx3-9AEpFXEDXxEeXI";
$telegramChatId  = "5863793961";
