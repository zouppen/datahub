<?php
$db = new SQLite3(__DIR__.'/../db/db.sqlite');
$db->busyTimeout(2000);
$db->exec('PRAGMA journal_mode = wal');

function err($msg) {
    error_log($msg);
    exit(1);
}

// Executes given database query. Terminates in case of a database
// error. When $error === NULL then errors are passed though to
// caller.
function db_execute(&$stmt, $values = [], $error = "Database error") {
    global $db;

    // Prepare statement for reuse
    $stmt->reset();
    
    // Bind values
    foreach ($values as $k=>$v) {
        // Numeric indices start from 1, increment needed
        if (!is_string($k)) $k++;
        $stmt->bindValue($k, $v);
    }

    // Execute and check result
    $ret = $stmt->execute();
    if ($error !== NULL && $ret === FALSE) err($error);
    return $ret;
}
