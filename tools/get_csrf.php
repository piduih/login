<?php
$path = $argv[1] ?? __DIR__ . '/../tmp_signup.html';
$s = @file_get_contents($path);
if(!$s){ echo ""; exit(0); }
if(preg_match('/meta name="csrf-token" content="([^"]+)"/',$s,$m)) echo $m[1];
