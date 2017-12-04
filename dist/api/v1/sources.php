<?php
header("Content-type: application/json; charset=utf-8");
require_once(__DIR__.'/../../../lib/common.php');
require_once(__DIR__.'/../../../lib/sensor_queries.php');

$out = [];
foreach ($sensors as $station => $axes) {
    $axis_names = [];
    foreach ($axes as $axis_name => $foo) {
        array_push($axis_names, $axis_name);
    }
    $out[$station] = $axis_names;
}

print(json_encode($out)."\n");
