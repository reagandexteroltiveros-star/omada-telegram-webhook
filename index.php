<?php
$rawData = file_get_contents("php://input");
file_put_contents("omada_debug.log", $rawData . PHP_EOL, FILE_APPEND);

echo "OK";
