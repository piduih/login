<?php
// tests/remember_flow.php
// End-to-end test for remember-me flow

$base = 'http://localhost:8000';
require_once __DIR__ . '/../config/db.php';

function ensure_test_user($db, $email, $username, $password){
    // create user if not exists, return id
    $stmt = $db->prepare('SELECT id FROM users WHERE email = :e LIMIT 1');
    $stmt->execute([':e' => $email]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if($u) return $u['id'];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $ins = $db->prepare('INSERT INTO users (username, email, password) VALUES (:u,:e,:p)');
    $ins->execute([':u' => $username, ':e' => $email, ':p' => $hash]);
    return $db->lastInsertId();
}

$email = 'remember_test@example.test';
$username = 'remember_test';
$password = 'TestPass1!';

$uid = ensure_test_user($db, $email, $username, $password);
echo "Using test user id=$uid email=$email\n";

$cookieJar = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'remember_test_cookies.txt';
@unlink($cookieJar);

// 1) GET login page to get CSRF and session cookie
$loginPage = $base . '/auth/login.php';
$ch = curl_init($loginPage);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$page = curl_exec($ch);
if($page === false){ echo "Failed to GET login page: " . curl_error($ch) . "\n"; exit(1); }
curl_close($ch);

// parse CSRF
$csrf = '';
if(preg_match('/<meta\s+name=["\']csrf-token["\']\s+content=["\']([^"\']+)["\']/', $page, $m)){
    $csrf = $m[1];
}
echo "CSRF token: " . ($csrf ?: '(not found)') . "\n";

if(!$csrf){ echo "Cannot proceed without CSRF token.\n"; exit(1); }

// 2) POST login with remember=1
$postUrl = $base . '/api/auth.php?action=login';
$ch = curl_init($postUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true); // capture headers
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => $email,
    'password' => $password,
    'remember' => '1',
    'action' => 'login',
    'csrf_token' => $csrf
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-CSRF-Token: ' . $csrf]);
$resp = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

// split headers and body
$header_size = $info['header_size'] ?? 0;
$headers = substr($resp, 0, $header_size);
$body = substr($resp, $header_size);
echo "Login HTTP code: " . $info['http_code'] . "\n";
echo "Login response body: $body\n";
echo "Login response headers:\n" . $headers . "\n";

// show any Set-Cookie headers
if(preg_match_all('/^Set-Cookie:\s*(.+)$/mi', $headers, $mc)){
    echo "Set-Cookie headers found:\n";
    foreach($mc[1] as $sc) echo " - " . trim($sc) . "\n";
} else {
    echo "No Set-Cookie headers in response.\n";
}

// 3) Inspect DB row for remember_token
$stmt = $db->prepare('SELECT id, remember_token, remember_expires FROM users WHERE email = :e LIMIT 1');
$stmt->execute([':e' => $email]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$u){ echo "User not found after login?\n"; exit(1); }

echo "DB remember_token: " . ($u['remember_token'] ?: '(null)') . "\n";
echo "remember_expires: " . ($u['remember_expires'] ?: '(null)') . "\n";

// Validate that remember_token looks like a SHA-256 hex (64 hex chars) when present
if($u['remember_token']){
    if(preg_match('/^[0-9a-f]{64}$/i', $u['remember_token'])){
        echo "DB stores a SHA-256 token hash (good).\n";
    } else {
        echo "DB remember_token does not look like a SHA-256 hex string (warning).\n";
    }
} else {
    echo "No remember token stored in DB.\n";
}

// 4) Read cookie jar to extract remember_token cookie value (fallback to headers)
$remember = '';
if(file_exists($cookieJar)){
    $lines = file($cookieJar, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach($lines as $line){
        if(substr($line,0,1) === '#') continue;
        $parts = preg_split('/\s+/', $line);
        // Netscape cookie file format: domain, flag, path, secure, expiration, name, value
        if(count($parts) >= 7){
            $name = $parts[5];
            $value = $parts[6];
            if($name === 'remember_token'){
                $remember = $value;
                break;
            }
        }
    }
}

// fallback: parse Set-Cookie header if cookie jar didn't save it
if(!$remember){
    if(!empty($headers) && preg_match('/Set-Cookie:\s*remember_token=([^;\s]+)/i', $headers, $m)){
        $remember = $m[1];
        echo "(Fallback) extracted remember token from Set-Cookie header.\n";
    }
}

if(!$remember){ echo "Remember cookie not found in cookie jar or headers.\n"; exit(1); }
echo "Remember cookie (raw): " . substr($remember,0,20) . "... (len=" . strlen($remember) . ")\n";

// 5) Call /api/auth.php?action=me with only the remember cookie (no PHPSESSID)
$ch = curl_init($base . '/api/auth.php?action=me');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Cookie: remember_token=' . $remember]);
$me = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);
echo "me endpoint HTTP code: " . $info['http_code'] . "\n";
echo "me response: $me\n";

// Debug: print cookie jar contents (do not delete so we can inspect)
if(file_exists($cookieJar)){
    echo "\n--- Cookie jar contents (" . $cookieJar . ") ---\n";
    echo file_get_contents($cookieJar) . "\n";
} else {
    echo "Cookie jar not present after login.\n";
}

echo "Test finished.\n";
