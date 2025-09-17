<?php
// Add columns for remember-me tokens if they don't exist
try {
    $db = new PDO('sqlite:' . __DIR__ . '/app.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $cols = $db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
    $names = array_column($cols, 'name');
    if(!in_array('remember_token', $names)){
        $db->exec("ALTER TABLE users ADD COLUMN remember_token TEXT");
        echo "Added remember_token\n";
    }
    if(!in_array('remember_expires', $names)){
        $db->exec("ALTER TABLE users ADD COLUMN remember_expires INTEGER");
        echo "Added remember_expires\n";
    }
    echo "Migration completed\n";
} catch (Exception $e){
    echo "Migration failed: " . $e->getMessage() . "\n";
}
