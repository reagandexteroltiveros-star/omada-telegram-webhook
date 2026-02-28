<?php
// Telegram settings
$botToken = "8414483455:AAGs6rmmLdkx-uFCkpx3-9AEpFXEDXxEeXI";
$chatId   = "5863793961";

// Read incoming webhook JSON
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// Default values
$eventType = $data['eventType'] ?? $data['event_type'] ?? 'Unknown Event';
$deviceName = $data['deviceName'] ?? $data['device_name'] ?? 'Unknown Device';
$siteName = $data['siteName'] ?? $data['site_name'] ?? 'Unknown Site';

// Target device/site
$mainDevice = 'EAP110';
$mainSite   = 'GELAI WIFI VOUCHER';

// Map events to friendly messages
switch (strtolower($eventType)) {
    case 'device online':
        $alert = "✅ Device Online";
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
    default:
        $alert = "ℹ️ $eventType";
        break;
}

// Highlight main device
if ($deviceName === $mainDevice && $siteName === $mainSite) {
    $alert = "🔥 $alert";
}

// Build Telegram message
$message  = "📡 OMADA ALERT\n\n";
$message .= "Event: $alert\n";
$message .= "Device: $deviceName\n";
$message .= "Site: $siteName\n";
$message .= "Time: " . date("Y-m-d H:i:s");

// Send Telegram alert
file_get_contents(
    "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($message)
);

// Respond OK to Omada
echo "OK";
