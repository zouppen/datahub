<?php
header("Content-type: text/csv; charset=utf-8");
require_once(__DIR__.'/common.php');

// Connect to the queue first
$context = new ZMQContext();
$subscriber = new ZMQSocket($context, ZMQ::SOCKET_SUB);
$subscriber->connect("ipc://".__DIR__."/updates.ipc");
$subscriber->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, "");
var_dump("ipc://".__DIR__."/updates.ipc");

// Prepare statements
$get_newer_stmt = $db->prepare("SELECT rowid, ts, value from temp WHERE rowid > ? LIMIT 20");

// Extract line from which to dump
$raw_row = @$_GET['r'];
$row = $raw_row === NULL ? $db->querySingle("SELECT max(rowid)-10 FROM temp") : intval($raw_row);
var_dump($from_row);
var_dump($row);

// Fetch historical data
$res = db_execute($get_newer_stmt, [$row]);
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
