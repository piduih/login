<?php
// tools/debug_session.php - debug: echo current session contents as JSON
if(session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json');
echo json_encode(['session_id' => session_id(), 'session' => $_SESSION]);
