<?php
// api/auth.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

session_start();

// Session expiration: if last activity older than TTL, destroy session
$session_ttl = 60 * 60 * 24 * 1; // 1 day by default
if(isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_ttl){
    session_unset(); session_destroy(); session_start();
}
$_SESSION['last_activity'] = time();

// Attempt remember-me login if session not set
if(!isset($_SESSION['user_id']) && !empty($_COOKIE['remember_token'])){
    $rtoken = $_COOKIE['remember_token'];
    // prefer comparing hashed token (new behavior)
    $rtoken_hash = hash('sha256', $rtoken);
    $stmt = $db->prepare('SELECT id, username, remember_expires, remember_token FROM users WHERE remember_token = :t LIMIT 1');
    $stmt->execute([':t' => $rtoken_hash]);
    $u = $stmt->fetch();
    if($u && $u['remember_expires'] >= time() && hash_equals($u['remember_token'], $rtoken_hash)){
        // matched hashed token; refresh expiry (sliding window)
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['username'] = $u['username'];
        try{
            $newExp = time() + (30 * 86400);
            $up = $db->prepare('UPDATE users SET remember_expires = :x WHERE id = :id');
            $up->execute([':x' => $newExp, ':id' => $u['id']]);
        } catch(Exception $e){ /* non-fatal */ }
    } else {
        // Fallback: some existing DB rows may contain plaintext tokens from before hashing change.
        // Try matching raw token to ease migration: if found, rotate to hashed storage.
        $stmt2 = $db->prepare('SELECT id, username, remember_expires, remember_token FROM users WHERE remember_token = :t LIMIT 1');
        $stmt2->execute([':t' => $rtoken]);
        $u2 = $stmt2->fetch();
        if($u2 && $u2['remember_expires'] >= time()){
            // rotate: issue a new token (this will store the hash in DB and set a new cookie)
            $_SESSION['user_id'] = $u2['id'];
            $_SESSION['username'] = $u2['username'];
            try{
                set_remember_cookie($u2['id'], $db, 30);
            } catch(Exception $e){ /* non-fatal */ }
        } else {
            // clear cookie if invalid
            setcookie('remember_token','', time() - 3600, '/', '', false, true);
        }
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
if (!$action) {
    json_response(['success' => false, 'message' => 'No action specified']);
}

try {
    if ($action === 'signup') {
        // require CSRF for signup
        if($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_csrf()) json_error('Invalid CSRF token', 403);
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$username || !$email || !$password) {
            json_error('Missing fields');
        }

        // check duplicates
        $stmt = $db->prepare('SELECT id FROM users WHERE email = :e OR username = :u LIMIT 1');
        $stmt->execute([':e' => $email, ':u' => $username]);
        if ($stmt->fetch()) json_error('Email or username already taken', 409);
        // password policy
        $pwErrors = validate_password_policy($password);
        if($pwErrors) json_error(implode('; ', $pwErrors), 422);

        $hash = hash_password($password);
        $stmt = $db->prepare('INSERT INTO users (username, email, password) VALUES (:u, :e, :p)');
        $stmt->execute([':u' => $username, ':e' => $email, ':p' => $hash]);
        json_response(['success' => true, 'message' => 'Account created']);

    } elseif ($action === 'login') {
        if($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_csrf()) json_error('Invalid CSRF token', 403);
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!$email || !$password) json_response(['success' => false, 'message' => 'Missing email or password']);

        $stmt = $db->prepare('SELECT * FROM users WHERE email = :e OR username = :e LIMIT 1');
        $stmt->execute([':e' => $email]);
        $user = $stmt->fetch();
        if (!$user || !verify_password($password, $user['password'])) {
            json_response(['success' => false, 'message' => 'Invalid credentials']);
        }
        // set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        // remember-me? if provided (POST param 'remember' == '1')
        if(!empty($_POST['remember']) && $_POST['remember'] == '1'){
            set_remember_cookie($user['id'], $db, 30);
        }
        json_response(['success' => true, 'message' => 'Logged in']);

    } elseif ($action === 'request_reset') {
        if($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_csrf()) json_error('Invalid CSRF token', 403);
        $email = trim($_POST['email'] ?? '');
        if (!$email) json_response(['success' => false, 'message' => 'Missing email']);
        // rate-limit reset requests per IP: max 5 per hour
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $rateFile = __DIR__ . '/../config/reset_rate.json';
        $rates = [];
        if(file_exists($rateFile)) $rates = json_decode(file_get_contents($rateFile), true) ?: [];
        $now = time();
        $rates[$ip] = array_filter($rates[$ip] ?? [], function($ts) use ($now){ return $ts > $now - 3600; });
        if(count($rates[$ip]) >= 5) json_error('Too many reset requests, try later', 429);
        $rates[$ip][] = $now;
        file_put_contents($rateFile, json_encode($rates));

        $stmt = $db->prepare('SELECT id FROM users WHERE email = :e LIMIT 1');
        $stmt->execute([':e' => $email]);
        $user = $stmt->fetch();
        if (!$user) json_response(['success' => true, 'message' => 'If that email exists, a reset link was sent (silent)']);

        $token = generate_token(24);
        $expires = time() + 3600; // 1 hour
        $stmt = $db->prepare('UPDATE users SET reset_token = :t, reset_expires = :x WHERE id = :id');
        $stmt->execute([':t' => $token, ':x' => $expires, ':id' => $user['id']]);

        $resetLink = sprintf('%s://%s%s/auth/reset.php?token=%s',
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http',
            $_SERVER['HTTP_HOST'],
            rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'),
            $token
        );

        $body = "Click the link to reset your password: $resetLink";
        send_email($email, 'Password reset', $body);
        json_response(['success' => true, 'message' => 'If that email exists, a reset link was sent (silent)']);

    } elseif ($action === 'reset_password') {
        // Can be called via GET to show token or POST to set a new password
        $token = $_REQUEST['token'] ?? '';
        if (!$token) json_response(['success' => false, 'message' => 'Missing token']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!verify_csrf()) json_error('Invalid CSRF token', 403);
            $password = $_POST['password'] ?? '';
            if (!$password) json_error('Missing password');

            $pwErrors = validate_password_policy($password);
            if($pwErrors) json_error(implode('; ', $pwErrors), 422);

            $stmt = $db->prepare('SELECT id, reset_expires FROM users WHERE reset_token = :t LIMIT 1');
            $stmt->execute([':t' => $token]);
            $user = $stmt->fetch();
            if (!$user || $user['reset_expires'] < time()) json_error('Token invalid or expired', 410);

            $hash = hash_password($password);
            $stmt = $db->prepare('UPDATE users SET password = :p, reset_token = NULL, reset_expires = NULL WHERE id = :id');
            $stmt->execute([':p' => $hash, ':id' => $user['id']]);
            json_response(['success' => true, 'message' => 'Password reset']);
        } else {
            // For GET we'll return JSON with token valid
            $stmt = $db->prepare('SELECT id, reset_expires FROM users WHERE reset_token = :t LIMIT 1');
            $stmt->execute([':t' => $token]);
            $user = $stmt->fetch();
            if (!$user || $user['reset_expires'] < time()) json_error('Token invalid or expired', 410);
            json_response(['success' => true, 'message' => 'Token valid', 'token' => $token]);
        }

    } elseif ($action === 'logout') {
        if($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_csrf()) json_error('Invalid CSRF token', 403);
        // clear remember token if present
        if(isset($_SESSION['user_id'])){
            clear_remember_cookie($db, $_SESSION['user_id']);
        }
        session_unset();
        session_destroy();
        json_response(['success' => true, 'message' => 'Logged out']);
    } elseif ($action === 'me') {
        if(!isset($_SESSION['user_id'])) json_response(['success' => false, 'message' => 'Not authenticated']);
        json_response(['success' => true, 'user' => ['id' => $_SESSION['user_id'], 'username' => $_SESSION['username']]]);
    }
} catch (Exception $e) {
    json_response(['success' => false, 'message' => $e->getMessage()]);
}
