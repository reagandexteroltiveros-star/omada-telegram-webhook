<?php
$botToken = "8414483455:AAGs6rmmLdkx-uFCkpx3-9AEpFXEDXxEeXI";
$chatId   = "5863793961";

$mainDevice = 'EAP110';
$mainSite   = 'GELAI WIFI VOUCHER';

// Omada API credentials
$omadaHost = "https://127.0.0.1:8043/#dashboardGlobal";
$username  = "REAGAN";
$password  = "Bbangel12398";

// 1️⃣ Authenticate with Omada API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$omadaHost/api/v2/login");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['username'=>$username,'password'=>$password]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$result = curl_exec($ch);
curl_close($ch);
$token = json_decode($result, true)['token'] ?? '';

// 2️⃣ Get device list
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$omadaHost/api/v2/sites");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
$sites = json_decode(curl_exec($ch), true);
curl_close($ch);

// 3️⃣ Find main device
foreach ($sites as $site) {
    if ($site['name'] === $mainSite) {
        foreach ($site['devices'] as $device) {
            if ($device['name'] === $mainDevice && $device['status'] !== 'online') {
                // Send Telegram alert
                $message  = "⚠️ OMADA ALERT\n\n";
                $message .= "Event: Device Offline / Heartbeat Missed\n";
                $message .= "Device: $mainDevice\n";
                $message .= "Site: $mainSite\n";
                $message .= "Time: " . date("Y-m-d H:i:s");
                file_get_contents(
                    "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($message)
                );
            }
        }
    }
}
