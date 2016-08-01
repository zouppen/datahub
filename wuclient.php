<?php
header("Content-type: text/csv; charset=utf-8");
require_once(__DIR__.'/common.php');

$page_size = 20;

// Connect to the queue first (new data may arrive while this script is running)
$context = new ZMQContext();
$subscriber = new ZMQSocket($context, ZMQ::SOCKET_SUB);
$subscriber->connect("ipc://".__DIR__."/updates.ipc");
$subscriber->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, "");

// Prepare statements
$get_newer_stmt = $db->prepare("SELECT rowid, ts, value from temp WHERE rowid > ? LIMIT $page_size");
$get_ptr_stmt = $db->prepare("SELECT rowid FROM temp WHERE ts >= ? LIMIT 1;");
$get_last_stmt = $db->prepare("SELECT MAX(rowid) FROM temp");

// TODO support query by date and add intelligent headers
// "since" query would return Location: rewrite to mathcnig rowid (rounded down to nearest 1000 for example

// Fetch historical data
$raw_row = @$_GET['r'];
$raw_ts = @$_GET['t'];

if ($raw_ts !== NULL) {
    // Search by timestamp

    // Atomically get pointer and latest rowid.
    $db->exec('BEGIN');
    $ptr = db_execute($get_ptr_stmt, [intval($raw_ts)])->fetchArray(SQLITE3_NUM)[0];
    $last = db_execute($get_last_stmt)->fetchArray(SQLITE3_NUM)[0];
    $db->exec('END');

    $final_ptr = $ptr === NULL ? $last : floor($ptr / $page_size) * $page_size - 1;
    header("Location: ?r=$final_ptr");
    exit(0);
} else if ($raw_row === NULL) {
    print("Provide either r or t parameter\n");
    exit(0);
}

$res = db_execute($get_newer_stmt, [intval($raw_row)]); // Use provided pointer
$got = FALSE;
while (true) {
    $row = $res->fetchArray(SQLITE3_NUM);
    if ($row === FALSE) break;
    $got = TRUE;
    print(implode(',',$row)."\n");
}

// If we didn't get any, listen for new data
if (!$got) {
    $row = $subscriber->recv();
    print($row."\n");
}
