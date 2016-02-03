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
 * array_parallel ? �w�肵���z��̗v�f�ɃR�[���o�b�N�֐����}���`�X���b�h�œK�p����
 * 
 * @param $data �R�[���o�b�N�֐���K�p����z��B
 * @param callback    �z��̊e�v�f�ɓK�p����R�[���o�b�N�֐��B
 * @param option    ���L�̃L�[�Œǉ��̃I�v�V�����𗘗p�ł���B
 *                  check_interval �X���b�h�����I���m�F�Ԋu�B�f�t�H���g��100usec
 *                  num_threads    �������s�X���b�h���B�f�t�H���g��4�X���b�h�B
 *                  reduce         �X���b�h�������ʌ��ʂ̊e�v�f��reduce�Őݒ肵�� callback �֐����J��Ԃ��K�p�������ʂ�߂�l�Ƃ���B
 *                                 �w�肳��Ȃ��ꍇ��$data�p�����[�^�Ɠ�����
 *                                 mixed callback ( mixed &$carry , Thread &$thread )
 *                                 $carry ���������̌��ʂ�ێ�����B
 *                                 $thread ���s���ꂽ�e�X���b�h�I�u�W�F�N�g�B���L�̃v���p�e�B�𗘗p�ł���B
 *                                         result ���s���ʁB
 *                                         exception ���s���ɔ���������O�B
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

        // �L�[�̌��̏��Ԃ�ۑ�����
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