<?php
// includes/helpers.php
function json_response($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

function generate_token($length = 48) {
    return bin2hex(random_bytes($length));
}

function validate_password_policy($password){
    $errors = [];
    if(strlen($password) < 8) $errors[] = 'Password must be at least 8 characters';
    if(!preg_match('/[A-Z]/', $password)) $errors[] = 'Include at least one uppercase letter';
    if(!preg_match('/[a-z]/', $password)) $errors[] = 'Include at least one lowercase letter';
    if(!preg_match('/[0-9]/', $password)) $errors[] = 'Include at least one number';
    if(!preg_match('/[^A-Za-z0-9]/', $password)) $errors[] = 'Include at least one special character';
    return $errors;
}

function json_error($message, $code = 400){
    http_response_code($code);
    json_response(['success' => false, 'message' => $message]);
}

function set_remember_cookie($userId, $db, $days = 30){
    // create a raw token for the cookie and store only its hash in the DB
    $token = generate_token(24);
    $token_hash = hash('sha256', $token);
    $expires = time() + ($days * 86400);
    $stmt = $db->prepare('UPDATE users SET remember_token = :t, remember_expires = :x WHERE id = :id');
    $stmt->execute([':t' => $token_hash, ':x' => $expires, ':id' => $userId]);
    // set raw token in HttpOnly cookie
    setcookie('remember_token', $token, $expires, '/', '', false, true);
}

function clear_remember_cookie($db, $userId = null){
    if($userId){
        $stmt = $db->prepare('UPDATE users SET remember_token = NULL, remember_expires = NULL WHERE id = :id');
        $stmt->execute([':id' => $userId]);
    }
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// CSRF helpers
function ensure_csrf(){
    if(session_status() !== PHP_SESSION_ACTIVE) session_start();
    if(empty($_SESSION['csrf_token'])){
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(){
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
    if(!$token || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)){
        return false;
    }
    return true;
}


// NOTE: For production, integrate an SMTP library. Here we stub send_email to log to a file.
function send_email($to, $subject, $body) {
    $log = sprintf("[%s] To:%s Subject:%s\n%s\n\n", date('c'), $to, $subject, $body);
    file_put_contents(__DIR__ . '/../text/email_log.txt', $log, FILE_APPEND | LOCK_EX);
    return true;
}
