<?php
// tests/login_test.php - simple login test (no remember)
$base = 'http://localhost:8000';
$cookieJar = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'login_test_cookies.txt';
@unlink($cookieJar);

$loginPage = $base . '/auth/login.php';
$ch = curl_init($loginPage);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$resp = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);
if($resp === false || $info['http_code'] !== 200){ echo "Failed to load login page\n"; exit(1); }
$body = substr($resp, $info['header_size']);
if(!preg_match('/<meta\s+name=["\']csrf-token["\']\s+content=["\']([^"\']+)["\']/', $body, $m)){
    echo "CSRF not found\n"; exit(1);
}
$csrf = $m[1];
echo "CSRF: $csrf\n";

$email = 'remember_test@example.test';
$password = 'TestPass1!';

$post = $base . '/api/auth.php';
$ch = curl_init($post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['action'=>'login','email'=>$email,'password'=>$password,'csrf_token'=>$csrf]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-CSRF-Token: ' . $csrf]);
$resp2 = curl_exec($ch);
$info2 = curl_getinfo($ch);
curl_close($ch);
$headers = substr($resp2, 0, $info2['header_size']);
$body2 = substr($resp2, $info2['header_size']);
echo "Login HTTP code: " . $info2['http_code'] . "\n";
echo "Login response headers:\n" . $headers . "\n";
echo "Login response body:\n" . $body2 . "\n";

if(file_exists($cookieJar)){
    echo "\nCookie jar:\n" . file_get_contents($cookieJar) . "\n";
}

@unlink($cookieJar);

echo "Done.\n";
