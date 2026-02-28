<?php
$webhooks = [
    [
        'name' => 'Omada Webhook',
        'type' => 'omada',
        'url' => 'https://omadawebhook.gamer.gd/index.php', // your webhook URL
        'payload_template' => 'omada',   // Omada only
        'webhook_id' => 'WH-001',
        'shared_secret' => 'shardSecret123',
        'retry_policy' => 3,
        'action' => 'active'
    ]
];