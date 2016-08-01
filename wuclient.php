<?php
header("Content-type: text/csv; charset=utf-8");
require_once(__DIR__.'/common.php');

// Connect to the queue first (new data may arrive while this script is running)
$context = new ZMQContext();
$subscriber = new ZMQSocket($context, ZMQ::SOCKET_SUB);
$subscriber->connect("ipc://".__DIR__."/updates.ipc");
$subscriber->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, "");

// Prepare statements
$get_newer_stmt = $db->prepare("SELECT rowid, ts, value from temp WHERE rowid > ? LIMIT 20");
$get_today_stmt = $db->prepare("SELECT * FROM temp WHERE ts >=strftime('%s','now','start of day')");

// TODO support query by date and add intelligent headers

// Fetch historical data
$raw_row = @$_GET['r'];
$res = $raw_row === NULL ?
    db_execute($get_today_stmt): // Get today's historic data
    db_execute($get_newer_stmt, [intval($raw_row)]); // Use provided pointer
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
