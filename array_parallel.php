<?php

class ArrayParallelThread extends \Thread{
    public $exception = null;
    public function __construct(&$callback){
        $this->callback = $callback;
    }
    public function run(){
        try{
            $callback = $this->callback;
            $this->result = $callback($this->args);
        } catch (Exception $e) {
            $this->exception = $e;
        }
    }
}


/**
 * array_parallel ? 指定した配列の要素にコールバック関数をマルチスレッドで適用する
 * 
 * @param $data コールバック関数を適用する配列。
 * @param callback    配列の各要素に適用するコールバック関数。
 * @param option    下記のキーで追加のオプションを利用できる。
 *                  check_interval スレッド処理終了確認間隔。デフォルトは100usec
 *                  num_threads    同時実行スレッド数。デフォルトは4スレッド。
 *                  reduce         スレッド処理結果結果の各要素にreduceで設定した callback 関数を繰り返し適用した結果を戻り値とする。
 *                                 指定されない場合は$dataパラメータと同数の
 *                                 mixed callback ( mixed &$carry , Thread &$thread )
 *                                 $carry 反復処理の結果を保持する。
 *                                 $thread 実行された各スレッドオブジェクト。下記のプロパティを利用できる。
 *                                         result 実行結果。
 *                                         exception 実行中に発生した例外。
 * 
 **/

function array_parallel($data , $callback , $option = [])
{
    $check_interval = 100;
    $num_threads    = isset($option["num_threads"]) ? $option["num_threads"] : 4;
    $initialize =  function(&$thread , $args){
                       $thread->args = $args;
                   };
    $reduce = (isset($option["reduce"]) ?
               $option["reduce"] :
               function(&$carry , &$thread){
                   if(isset($threads->exception)) error_log($e);
                   $carry[$thread->getThreadId()] = $thread->result;
               });
    
    $order   = [];
    $threads = [];
    $result  = null;

    $checkComplete = function($thread) use (&$threads , &$result, &$reduce){
        if($thread->isRunning()) return false;
        $reduce($result , $thread);
        unset($threads[$thread->getThreadId()]);
        return true;
    };
    
    while(1){
        if(count($threads) >= $num_threads){
            usleep($check_interval);
            array_walk($threads , $checkComplete);
            continue;
        }
        list($key, $datum) = each($data);
        $newThread = new ArrayParallelThread($callback);
        $newThread->args = $args;
        
        $newThread->start();
        $threadId = $newThread->getThreadId();
        $threads[$threadId] = $newThread;

        // キーの元の順番を保存する
        $order[$key] = $threadId;
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