<?php
// // 1. Define the log file path
// $logFile = 'zkteco_full_dump.log';

// // 2. Capture the Request Metadata
// $method = $_SERVER['REQUEST_METHOD'];
// $uri    = $_SERVER['REQUEST_URI'];
// $time   = date('Y-m-d H:i:s');

// // 3. Capture all HTTP Headers
// $headers = "";
// foreach (getallheaders() as $name => $value) {
//     $headers .= "$name: $value\n";
// }

// // 4. Capture the Body (This is where the user/attendance data is)
// $body = file_get_contents('php://input');

// // 5. Format the log entry
// $logEntry = "========================================\n";
// $logEntry .= "TIME: $time\n";
// $logEntry .= "METHOD: $method\n";
// $logEntry .= "URI: $uri\n";
// $logEntry .= "------------------- HEADERS -------------------\n";
// $logEntry .= $headers;
// $logEntry .= "-------------------- BODY ---------------------\n";
// $logEntry .= $body . "\n";
// $logEntry .= "========================================\n\n";

// // 6. Save to file (Append mode)
// file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

// // 7. IMPORTANT: Respond to the device
// // ZKTeco devices usually look for a plain text "OK" to confirm receipt
// header("Content-Type: text/plain");
// echo "OK";


 
$logFile = dirname(__FILE__) . '/zkteco_full_dump.log';

$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];
$time   = date('Y-m-d H:i:s');

$headers = "";
foreach (getallheaders() as $name => $value) {
    $headers .= "$name: $value\n";
}

$body = file_get_contents('php://input');

$logEntry = "========================================\n";
$logEntry .= "TIME: $time\n";
$logEntry .= "METHOD: $method\n";
$logEntry .= "URI: $uri\n";
$logEntry .= "HEADERS:\n$headers";
$logEntry .= "BODY:\n$body\n";
$logEntry .= "========================================\n\n";

file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

// Proper ADMS handshake response
$sn = isset($_GET['SN']) ? $_GET['SN'] : '';
$date = date('Y-m-d H:i:s');

header("Content-Type: text/plain");

if (strpos($uri, 'cdata') !== false && $method == 'GET') {
    echo "GET OPTION FROM:{$sn}\n";
    echo "ATTLOGStamp=0\n";
    echo "OPERLOGStamp=0\n";
    echo "ATTPHOTOStamp=0\n";
    echo "ErrorDelay=30\n";
    echo "Delay=10\n";
    echo "TransTimes=00:00;14:05\n";
    echo "TransInterval=1\n";
    echo "TransFlag=TransData AttLog\n";
    echo "Realtime=1\n";
    echo "Encrypt=0\n";
} else {
    echo "OK";
}