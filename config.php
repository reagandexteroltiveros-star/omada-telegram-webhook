<?php
include 'config.php';

$omadaControllerUrl = 'https://127.0.0.1:8043';
$omadaUsername      = 'REAGAN';
$omadaPassword      = 'Bbangel12398!';
$siteName           = 'GELAI VOUCHER WIFI';
$deviceName         = 'EAP110';

// Login to Omada Controller
$loginPayload = json_encode(['username' => $omadaUsername, 'password' => $omadaPassword]);
$ch = curl_init("$omadaControllerUrl/api/v2/login");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginPayload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if (!isset($data['token'])) die("Cannot login to Omada Controller\n");
$token = $data['token'];

// Get sites
$ch = curl_init("$omadaControllerUrl/api/v2/sites");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$sitesResponse = curl_exec($ch);
curl_close($ch);

$sites = json_decode($sitesResponse, true)['data'];
$siteId = null;
foreach ($sites as $site) {
    if ($site['name'] === $siteName) $siteId = $site['id'];
}
if (!$siteId) die("Site not found\n");

// Get devices in site
$ch = curl_init("$omadaControllerUrl/api/v2/sites/$siteId/devices");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$devicesResponse = curl_exec($ch);
curl_close($ch);

$devices = json_decode($devicesResponse, true)['data'];
$targetDevice = null;
foreach ($devices as $device) {
    if ($device['name'] === $deviceName) $targetDevice = $device;
}
if (!$targetDevice) die("Device not found\n");

// Check device status
$status = strtolower($targetDevice['status']);
if ($status !== 'online') {
    // Build alert payload
    $alertPayload = [
        'event_type'  => 'heartbeat_missed',
        'device_name' => $deviceName,
        'site_name'   => $siteName,
        'description' => "Device $deviceName is offline or heartbeat missed",
        'timestamp'   => date('c')
    ];

    // Send to index.php webhook
    foreach ($webhooks as $hook) {
        if ($hook['action'] !== 'active') continue;
        if ($hook['payload_template'] === 'omada') {
            $ch = curl_init($hook['url']);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($alertPayload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);
        }
    }

    // Send Telegram directly
    $telegramMessage = "🔥 OMADA ALERT 🔥\nHeartbeat missed / Offline detected\nDevice: $deviceName\nSite: $siteName\nTime: " . date("Y-m-d H:i:s");
    $telegramUrl = "https://api.telegram.org/bot{$telegramBotToken}/sendMessage?chat_id={$telegramChatId}&text=" . urlencode($telegramMessage);
    file_get_contents($telegramUrl);

    echo "Alert sent for $deviceName: $status\n";
} else {
    echo "$deviceName is online.\n";
}
