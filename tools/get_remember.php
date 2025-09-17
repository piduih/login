<?php
$db=new PDO('sqlite:'.__DIR__.'/../config/app.db');
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$stmt=$db->prepare('SELECT remember_token FROM users WHERE email=:e');
$stmt->execute([':e'=>'test@example.com']);
$r=$stmt->fetch(PDO::FETCH_ASSOC);
echo $r['remember_token'] ?? 'NONE';
