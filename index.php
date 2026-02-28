<?php
// DEBUG LOGGER
$rawData = file_get_contents("php://input");

// Save the raw JSON payload
file_put_contents("omada_payload_log.json", $rawData . PHP_EOL, FILE_APPEND);

// Also log to Render logs (stdout)
error_log("OMADA PAYLOAD: " . $rawData);

// Respond OK
echo "OK";
