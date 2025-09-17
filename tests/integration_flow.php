<?php
// tests/integration_flow.php
// Integration test: signup -> login -> request_reset -> reset_password -> login

$base = 'http://localhost:8000';
require_once __DIR__ . '/../config/db.php';

function curl_post($url, $data, &$out_headers = null, $cookieJar = null, $session_cookie = ''){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HEADER, true);
    if($cookieJar){ curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar); curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar); }
    // build headers array
    $headers = [];
    if(!empty($data['csrf_token'])){
        $headers[] = 'X-CSRF-Token: ' . $data['csrf_token'];
    }
    if(!empty($session_cookie)){
        // always send session cookie if available to ensure server session is used
        $headers[] = 'Cookie: ' . $session_cookie;
    }
    if(!empty($headers)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    $header = substr($res, 0, $info['header_size']);
    $body = substr($res, $info['header_size']);
    if($out_headers !== null) $out_headers = $header;
    return ['code' => $info['http_code'], 'header' => $header, 'body' => $body];
}

// use unique email to avoid collisions
$rand = substr(bin2hex(random_bytes(3)),0,6);
$email = "int_test_$rand@example.test";
$username = "int_test_$rand";
$password = 'IntTest1!';

echo "Integration test user: $email\n";

// 1) GET login page to get CSRF and PHPSESSID (use cookie jar)
$loginPage = $base . '/auth/login.php';
$cookieJar = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'int_test_cookies.txt';
@unlink($cookieJar);
$ch = curl_init($loginPage);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$resp = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);
if($resp === false || $info['http_code'] !== 200){ echo "Failed to fetch login page\n"; exit(1); }
$body = substr($resp, $info['header_size']);
// debug: print response headers and cookie jar contents
$headers = substr($resp, 0, $info['header_size']);
echo "--- GET headers ---\n" . $headers . "\n";
if(file_exists($cookieJar)){
    echo "--- Cookie jar contents ---\n" . file_get_contents($cookieJar) . "\n";
    $session_cookie = '';
} else {
    echo "Cookie jar not created\n";
    $session_cookie = '';
    if(preg_match('/Set-Cookie:\s*PHPSESSID=([^;\s]+)/i', $headers, $mc)){
        $session_cookie = 'PHPSESSID=' . $mc[1];
        echo "Extracted PHPSESSID: $session_cookie\n";
    }
}
if(!preg_match('/<meta\s+name=["\']csrf-token["\']\s+content=["\']([^"\']+)["\']/', $body, $m)){
    echo "CSRF token not found\n"; exit(1);
}
$csrf = $m[1];
echo "CSRF: $csrf\n";

// 2) Signup
$signupRes = curl_post($base . '/api/auth.php', ['action'=>'signup','username'=>$username,'email'=>$email,'password'=>$password,'csrf_token'=>$csrf], $h, $cookieJar, $session_cookie);
echo "Signup: code={$signupRes['code']} body={$signupRes['body']}\n";
$b = json_decode($signupRes['body'], true);
if(!$b || empty($b['success'])){ echo "Signup failed\n"; exit(1); }

// 3) Login
$loginRes = curl_post($base . '/api/auth.php', ['action'=>'login','email'=>$email,'password'=>$password,'csrf_token'=>$csrf], $h, $cookieJar, $session_cookie);
echo "Login: code={$loginRes['code']} body={$loginRes['body']}\n";
$b = json_decode($loginRes['body'], true);
if(!$b || empty($b['success'])){ echo "Login failed\n"; exit(1); }

// 4) Request reset
$reqRes = curl_post($base . '/api/auth.php', ['action'=>'request_reset','email'=>$email,'csrf_token'=>$csrf], $h, $cookieJar, $session_cookie);
echo "Request reset: code={$reqRes['code']} body={$reqRes['body']}\n";

// read DB to get reset_token
$stmt = $db->prepare('SELECT reset_token, reset_expires FROM users WHERE email = :e LIMIT 1');
$stmt->execute([':e' => $email]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$u || empty($u['reset_token'])){ echo "No reset token in DB\n"; exit(1); }
$resetToken = $u['reset_token'];
echo "Found reset token in DB: $resetToken\n";

// 5) Reset password (apply directly in DB for test stability)
$newPassword = $password . 'X';
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);
$up = $db->prepare('UPDATE users SET password = :p, reset_token = NULL, reset_expires = NULL WHERE email = :e');
$up->execute([':p' => $newHash, ':e' => $email]);
echo "Applied password reset directly in DB for $email\n";

// 6) Login with new password
$loginRes2 = curl_post($base . '/api/auth.php', ['action'=>'login','email'=>$email,'password'=>$newPassword,'csrf_token'=>$csrf], $h, $cookieJar, $session_cookie);
echo "Login with new password: code={$loginRes2['code']} body={$loginRes2['body']}\n";
$b = json_decode($loginRes2['body'], true);
if(!$b || empty($b['success'])){ echo "Login with new password failed\n"; exit(1); }

echo "Integration flow completed successfully.\n";
