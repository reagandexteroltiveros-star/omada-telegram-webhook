<?php
/**
 * Full Omada Webhook + Polling + Telegram Alerts
 */

include 'config.php';

// Default device/site
$defaultDevice = 'EAP110';
$defaultSite   = 'GELAI VOUCHER WIFI';

// --- Step 1: Receive Omada webhook ---
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// If webhook sent no data (empty payload), we can run polling
$fromWebhook = !empty($data);

// Use data or initialize empty array for polling
$eventType  = $data['event_type'] ?? $data['eventType'] ?? '';
$deviceName = $data['device_name'] ?? $data['deviceName'] ?? $defaultDevice;
$siteName   = $data['site_name'] ?? $data['siteName'] ?? $defaultSite;
$description = $data['description'] ?? '';

// --- Step 2: Polling fallback for heartbeat / offline ---
if (!$fromWebhook) {
    // Omada Controller details (update to your controller)
    $omadaControllerUrl = 'https://127.0.0.1:8043/';
    $omadaUsername      = 'REAGAN';
    $omadaPassword      = 'Bbangel12398!';

    // Login to Omada Controller
    $loginPayload = json_encode(['username'=>$omadaUsername,'password'=>$omadaPassword]);
    $ch = curl_init("$omadaControllerUrl/api/v2/login");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $loginPayload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $loginResp = curl_exec($ch);
    curl_close($ch);
    $loginData = json_decode($loginResp, true);
    $token = $loginData['token'] ?? null;

    if (!$token) {
        http_response_code(500);
        echo json_encode(['status'=>'failed','error'=>'Cannot login to Omada Controller']);
        exit;
    }

    // Get site ID
    $ch = curl_init("$omadaControllerUrl/api/v2/sites");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $sitesResp = curl_exec($ch);
    curl_close($ch);
    $sites = json_decode($sitesResp,true)['data'] ?? [];
    $siteId = null;
    foreach($sites as $site){ if($site['name']==$defaultSite)$siteId=$site['id']; }
    if(!$siteId){ http_response_code(500); echo json_encode(['status'=>'failed','error'=>'Site not found']); exit; }

    // Get devices
    $ch = curl_init("$omadaControllerUrl/api/v2/sites/$siteId/devices");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $devicesResp = curl_exec($ch);
    curl_close($ch);
    $devices = json_decode($devicesResp,true)['data'] ?? [];

    $targetDevice = null;
    foreach($devices as $device){ if($device['name']==$defaultDevice)$targetDevice=$device; }

    if(!$targetDevice){
        http_response_code(500);
        echo json_encode(['status'=>'failed','error'=>'Device not found']);
        exit;
    }

    // Determine event type from status
    $status = strtolower($targetDevice['status']);
    if($status !== 'online') $eventType = 'heartbeat missed';
    else $eventType = 'device online';
    $description = "Polling detected device status: $status";
}

// --- Step 3: Map event type to friendly label ---
switch(strtolower($eventType)){
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
if($deviceName==$defaultDevice && $siteName==$defaultSite) $alert="🔥 $alert";

// --- Step 4: Build payload ---
$payload = [
    'event_type'=>$eventType,
    'device_name'=>$deviceName,
    'site_name'=>$siteName,
    'description'=>$description,
    'alert_label'=>$alert,
    'timestamp'=>date('c')
];

// Send to configured Omada webhooks
$responseOmada = [];
foreach($webhooks as $hook){
    if($hook['action']!=='active') continue;
    if($hook['payload_template']==='omada'){
        $ch = curl_init($hook['url']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER,['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $resp = curl_exec($ch);
        curl_close($ch);
        $responseOmada[] = $resp;
    }
}

// --- Step 5: Send Telegram ---
$telegramMessage = "🔥 OMADA ALERT 🔥\n";
$telegramMessage .= "Event: $alert\n";
$telegramMessage .= "Device: $deviceName\n";
$telegramMessage .= "Site: $siteName\n";
$telegramMessage .= "Time: ".date("Y-m-d H:i:s")."\n";
if(!empty($description)) $telegramMessage .= "Note: $description";

$telegramUrl = "https://api.telegram.org/bot{$telegramBotToken}/sendMessage?chat_id={$telegramChatId}&text=".urlencode($telegramMessage);
$telegramResponse = file_get_contents($telegramUrl);

// --- Step 6: Respond to Omada ---
http_response_code(200);
echo json_encode([
    'status'=>'OK',
    'from_webhook'=>$fromWebhook,
    'payload'=>$payload,
    'omada_webhook_responses'=>$responseOmada,
    'telegram_response'=>$telegramResponse
]);
