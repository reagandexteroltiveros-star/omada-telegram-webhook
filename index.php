<?php
include 'config.php';

// Capture raw Omada payload
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// Extract event info
$eventType  = $data['event_type'] ?? $data['eventType'] ?? 'Unknown Event';
$deviceName = $data['device_name'] ?? $data['deviceName'] ?? 'Unknown Device';
$siteName   = $data['site_name'] ?? $data['siteName'] ?? 'Unknown Site';
$description = $data['description'] ?? '';

// Target device/site highlighting
$mainDevice = 'EAP110';
$mainSite   = 'GELAI WIFI VOUCHER';

// Map events to friendly labels
switch (strtolower($eventType)) {
    case 'device online': $alert = "✅ Device Online"; break;
    case 'device offline':
    case 'heartbeat missed': $alert = "⚠️ Heartbeat Missed"; break;
    case 'adopted': $alert = "📥 Device Adopted"; break;
    case 'rebooted': $alert = "🔄 Device Rebooted"; break;
    case 'adopting': $alert = "⏳ Device Adopting"; break;
    case 'provisioned':
    case 'provisioning': $alert = "⚙️ Device Provisioning"; break;
    default: $alert = "ℹ️ $eventType"; break;
}

// Highlight main device/site
if ($deviceName === $mainDevice && $siteName === $mainSite) {
    $alert = "🔥 $alert";
}

// Build Omada payload (for any Omada webhook endpoints)
$payload = [
    'event_type'  => $eventType,
    'device_name' => $deviceName,
    'site_name'   => $siteName,
    'description' => $description,
    'alert_label' => $alert,
    'timestamp'   => date('c')
];

// Send payload to all active Omada webhook endpoints
foreach ($webhooks as $hook) {
    if ($hook['action'] !== 'active') continue;
    if ($hook['payload_template'] === 'omada') {
        $ch = curl_init($hook['url']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}

// --- Telegram alert ---
$telegramMessage = "🔥 OMADA ALERT 🔥\n";
$telegramMessage .= "Event: $alert\n";
$telegramMessage .= "Device: $deviceName\n";
$telegramMessage .= "Site: $siteName\n";
$telegramMessage .= "Time: " . date("Y-m-d H:i:s") . "\n";
if (!empty($description)) $telegramMessage .= "Note: $description";

$telegramUrl = "https://api.telegram.org/bot{$telegramBotToken}/sendMessage?chat_id={$telegramChatId}&text=" . urlencode($telegramMessage);
file_get_contents($telegramUrl);

// Respond OK to Omada
http_response_code(200);
echo json_encode(['status' => 'OK']);
