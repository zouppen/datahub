<?php

$sensors = json_decode(file_get_contents(__DIR__.'/../sensors.json'));

/**
 * Returns prepared SQL statement for given source value.
 */
function prepare_sensor_query($o) {
    global $db;
    
    $i = 0;
    $sql = 'SELECT ts, t.value FROM point p ';
    $data = [
        ':s' => $o->source, // Data source
        ':k' => $o->show,   // Key of desired attribute
    ];
    
    // Inner join every attribute which is required to match
    foreach ($o->match as $k => $v) {
        $sql .= match_join($i);
        $data[":k$i"] = $k; // Key name of an attribute
        $data[":v$i"] = $v; // and required value
        $i++;
    }

    // Join desired attribute and add source and time range limits
    $sql .=
        "JOIN point_data t ON (p.rowid = t.point AND t.key=:k) ".
        "WHERE source=:s AND ts BETWEEN :a AND :b ORDER BY ts";

    // Finally, prepare statement and bind all parameters but time range.
    $stmt = $db->prepare($sql);
    foreach ($data as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    return $stmt;
}

function match_join($i) {
    return "JOIN point_data t$i ON (p.rowid = t$i.point AND t$i.key=:k$i AND t$i.value=:v$i) ";
}
