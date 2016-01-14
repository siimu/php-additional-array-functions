<?php

include(__DIR__."/../array_parallel.php");

$data = [1,2,'333'=>3,4,"bbb"=>5,6,7,8];
echo "origin:";
var_export($data);
$start = microtime(1);

$mul = 3;
$result = array_parallel($data , function($a) use ($mul){
    echo "start:" , $a,"\n";
    sleep(rand(1,5));
    echo "complete:" , $a,"\n";
    return $a * $mul;
},
["num_threads" => 4 ,
// 'reduce'=>function(&$carry , &$thread){ $carry += $thread->result;}
 ]);
echo "result:", " " , var_export($result , 1) , "\n";

echo "elapse:", " " , microtime(1) - $start , "sec.\n";
 ;

var_export($data);
$data = [1,2,'333'=>3,4,"bbb"=>5,6,7,8];

$mul = 3;
$result = array_parallel($data ,
                         function($a) use ($mul){
                             echo "start:" , $a,"\n";
                             echo "complete:" , $a,"\n";
                             return $a * $mul;
                         },
                         ["num_threads" => 4 ,
                          'reduce'=>function(&$carry , &$thread){
                              var_dump( "yy");
                              echo __LINE__ ;
                              $carry += $thread->result;
                          }
                          ]);
echo "result:", " " , var_export($result , 1) , "\n";

echo "elapse:", " " , microtime(1) - $start , "sec.\n";
