<?php

class ArrayParallelThread extends Thread{
    public function __construct(&$func){
        $this->func = $func;
    }
    public function run(){
        $func = $this->func;
        $this->result = $func($this->args);
    }
}

function array_parallel(&$data , $func , $option = [])
{
    $check_interval = 100;
    $num_threads    = isset($option["num_threads"]) ? $option["num_threads"] : 4; 
    $reduce = (isset($option["reduce"]) ?
               $option["reduce"] :
               function(&$carry , &$thread){
                   $carry[$thread->getThreadId()] = $thread->result;
               });
    
    $order   = [];
    $threads = [];
    $result  = null;

    $checkComplete = function($thread) use (&$threads , &$result, &$reduce){
        if($thread->isRunning()) return;
        $reduce($result , $thread);
        unset($threads[$thread->getThreadId()]);
    };
    
    while(1){
        if(count($threads) >= $num_threads){
            usleep($check_interval);
            array_walk($threads , $checkComplete);
            continue;
        }
        list($key,$datum) = each($data);
        if(!$datum) break;
        $newThread = new ArrayParallelThread($func);
        $newThread->args = $datum;
        $newThread->start();
        $threadId = $newThread->getThreadId();
        $order[$key] = $threadId;
        $threads[$threadId] = $newThread;
    }
    
    while ($threads) {
        array_walk($threads , $checkComplete);
        usleep($check_interval);
    }

    if(isset($option["reduce"]))return $result;

    $ret = [];
    foreach($order as $key => $tid) $ret[$key] = $result[$tid];

    return $ret;

}