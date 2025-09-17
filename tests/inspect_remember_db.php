<?php
// tests/inspect_remember_db.php
require_once __DIR__ . '/../config/db.php';

$email = 'remember_test@example.test';
$stmt = $db->prepare('SELECT id, username, remember_token, LENGTH(remember_token) as len, remember_expires FROM users WHERE email = :e LIMIT 1');
$stmt->execute([':e' => $email]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$u){
    echo 'User not found: ' . $email . PHP_EOL;
    exit(1);
}

echo 'id=' . $u['id'] . ' username=' . $u['username'] . PHP_EOL;
echo 'remember_token present: ' . ($u['remember_token'] ? 'yes' : 'no') . PHP_EOL;
echo 'remember_token length: ' . ($u['len'] ?? strlen($u['remember_token'])) . PHP_EOL;
echo 'remember_token (raw): ' . ($u['remember_token'] ?: '(null)') . PHP_EOL;
echo 'remember_expires: ' . ($u['remember_expires'] ?: '(null)') . PHP_EOL;
