<?php
/**
 * Omada Webhook + Telegram Alerts
 * Handles Device Online/Offline, Heartbeat Missed, Adopted, Rebooted, Adopting, Provisioning
 * Logs raw payload to debug and fix "Unknown Event" issue
 */


$rawData = file_get_contents("php://input");
file_put_contents(__DIR__.'/omada_debug_log.txt', $rawData . "\n", FILE_APPEND);
http_response_code(200);
echo "OK";
// Telegram bot config
$telegramBotToken = "8414483455:AAGs6rmmLdkx-uFCkpx3-9AEpFXEDXxEeXI";
$telegramChatId   = "5863793961";

// Default device/site for fallback
$defaultDevice = 'EAP110-OUTDOOR | TP-LINK OMADA';
$defaultSite   = 'GELAI VOUCHER WIFI';

// Log received payload for debugging
$logFile = '/var/www/html/omada_webhook_log.txt'; // Change this to your server's writable directory
$rawData = file_get_contents("php://input");
file_put_contents($logFile, "Received payload: " . $rawData . "\n", FILE_APPEND);

// Decode the raw payload
$data = json_decode($rawData, true);

// If no data is received, log and return an error
if (!$data) {
    file_put_contents($logFile, "Invalid payload received.\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['status' => 'failed', 'error' => 'Invalid or empty payload']);
    exit;
}

// Extract values with defaults
$eventType  = strtolower($data['event_type'] ?? $data['eventType'] ?? 'unknown event');
$deviceName = $data['device_name'] ?? $data['deviceName'] ?? $defaultDevice;
$siteName   = $data['site_name'] ?? $data['siteName'] ?? $defaultSite;
$description = $data['description'] ?? 'No description provided';

// Log extracted values for debugging
file_put_contents($logFile, "Extracted: EventType: $eventType, Device: $deviceName, Site: $siteName\n", FILE_APPEND);

// Map event types to friendly labels
switch($eventType) {
    case 'device online': 
        $alert = "✅ Device Online"; 
        break;
    case 'device offline':
    case 'heartbeat missed': 
        $alert = "⚠️ Heartbeat Missed"; 
        break;
    case 'adopted': 
        $alert = "📥 Device Adopted"; 
        break;
    case 'rebooted': 
        $alert = "🔄 Device Rebooted"; 
        break;
    case 'adopting': 
        $alert = "⏳ Device Adopting"; 
        break;
    case 'provisioned':
    case 'provisioning': 
        $alert = "⚙️ Device Provisioning"; 
        break;
    case 'client connected':
        $alert = "📶 Client Connected"; 
        break;
    case 'client disconnected':
        $alert = "📴 Client Disconnected"; 
        break;
    default: 
        $alert = "ℹ️ Unknown Event"; 
        break;
}

// Highlight main device/site
if ($deviceName === $defaultDevice && $siteName === $defaultSite) {
    $alert = "🔥 $alert";
}

// Build Telegram message
$telegramMessage = "🔥 OMADA ALERT 🔥\n";
$telegramMessage .= "Event: $alert\n";
$telegramMessage .= "Device: $deviceName\n";
$telegramMessage .= "Site: $siteName\n";
$telegramMessage .= "Time: " . date("Y-m-d H:i:s") . "\n";
$telegramMessage .= "Note: $description";

// Log Telegram message
file_put_contents($logFile, "Telegram Message: " . $telegramMessage . "\n", FILE_APPEND);

// Send to Telegram bot
$telegramUrl = "https://api.telegram.org/bot{$telegramBotToken}/sendMessage?chat_id={$telegramChatId}&text=" . urlencode($telegramMessage);
$telegramResponse = file_get_contents($telegramUrl);

// Respond to Omada
http_response_code(200);
echo json_encode([
    'status' => 'OK',
    'payload_received' => $data,
    'telegram_message_sent' => $telegramMessage,
    'telegram_api_response' => $telegramResponse
]);
