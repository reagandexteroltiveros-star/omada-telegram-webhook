<?php
/**
 * Omada Webhook + Telegram Alerts
 * Handles Device Online/Offline, Heartbeat Missed, Adopted, Rebooted, Adopting, Provisioning
 * Excludes client name for client-related events
 */

// Telegram bot config
$telegramBotToken = "8414483455:AAGs6rmmLdkx-uFCkpx3-9AEpFXEDXxEeXI";
$telegramChatId   = "5863793961";

// Default device/site for fallback
$defaultDevice = 'EAP110-Outdoor | TP-LINK OMADA';
$defaultSite   = 'GELAI VOUCHER WIFI';

// Capture Omada payload
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// Extract info with defaults
$eventType  = strtolower($data['event_type'] ?? $data['eventType'] ?? 'unknown event');
$deviceName = $data['device_name'] ?? $data['deviceName'] ?? $defaultDevice;
$siteName   = $data['site_name'] ?? $data['siteName'] ?? $defaultSite;
$description = $data['description'] ?? 'No description provided';

// Map event types to friendly labels
switch($eventType) {
    case 'online': 
        $alert = "✅ Device Online"; 
        break;
    case 'disconnected':
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
        $alert = "ℹ️ $eventType"; 
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

// Send to Telegram
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
