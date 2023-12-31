<?php

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET');
header('Content-Type: application/json; charset=utf-8');
echo " ";
require_once("../../../token/auth/auth/auth.php");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {    
    return 0;    
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once("participantes.php");
} else {
    http_response_code(405);
    echo json_encode(array('GET' => false));
    exit();
}
?>