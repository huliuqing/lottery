<?php

$settings = array(
    array("pid" => 1, 'weights' => 60, 'score' => 1),
    array("pid" => 2, 'weights' => 30, 'score' => 2),
    array("pid" => 3, 'weights' => 10, 'score' => 3),
);

// $lottery = Lottery::make()->setFields('pid', 'weights')->go($settings);

$lottery = Lottery::make($config = array("indexField" => 'pid', "weightField" => "weights", "debug" => true));

$result = array();

$i = 0;
while ( $i < 100) {
	$prize = $lottery->goPro($settings);
	$result[$prize['pid']] = isset($result[$prize['pid']]) ? $result[$prize['pid']] + 1: 1;
	$i++;	
}

var_dump($result);
