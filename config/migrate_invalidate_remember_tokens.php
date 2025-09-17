<?php
// migrate_invalidate_remember_tokens.php
// Simple migration to clear any existing remember tokens (plaintext or otherwise)
require_once __DIR__ . '/db.php';

try{
    $db->beginTransaction();
    $stmt = $db->prepare('UPDATE users SET remember_token = NULL, remember_expires = NULL');
    $stmt->execute();
    $db->commit();
    echo "Migration applied: cleared remember_token and remember_expires for all users.\n";
} catch (Exception $e){
    if($db->inTransaction()) $db->rollBack();
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

// small verification output
$rows = $db->query('SELECT id, username, remember_token IS NOT NULL AS has_token, remember_expires FROM users')->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r){
    echo sprintf("id=%s user=%s has_token=%s expires=%s\n", $r['id'], $r['username'], $r['has_token'] ? '1' : '0', $r['remember_expires']);
}

echo "Done.\n";
