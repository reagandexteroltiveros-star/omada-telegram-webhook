<?php
// Path to the omada_debug_log.txt file
$logFile = __DIR__ . '/omada_debug_log.txt';

// Check if the log file exists
if (file_exists($logFile)) {
    // Set the correct headers for downloading the file
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="omada_debug_log.txt"');
    header('Content-Length: ' . filesize($logFile));
    
    // Read and output the file content
    readfile($logFile);
    exit;
} else {
    echo "Log file does not exist.";
}
?>
