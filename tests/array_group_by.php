<?php
include(__DIR__."/../array_group.php");

$data = [["type" => "a", "name" => "AAA","age"=>16],
         ["type" => "b", "name" => "BBB","age"=>14],
         ["type" => "a", "name" => "AAB","age"=>24],
         ["type" => "b", "name" => "BBA","age"=>14],
         ];
echo "source:";
var_dump($data);

echo "\ngroup by type:";
var_dump(array_group_by($data, "type"));

//echo "\ngroup by age:";
var_dump(array_group_by($data, function($x){ return $x["age"] >=20 ? "adult" : "child" ;}));

