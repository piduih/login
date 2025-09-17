<?php
$db=new PDO('sqlite:'.__DIR__.'/../config/app.db');
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
foreach($db->query('SELECT id,username,email,remember_token,remember_expires FROM users') as $row) print_r($row);
