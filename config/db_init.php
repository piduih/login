<?php
// db_init.php: Initialize SQLite database and create users table
$db = new SQLite3(__DIR__ . '/../config/app.db');
$db->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    reset_token TEXT,
    reset_expires INTEGER
)');
echo "Database and users table initialized.";
