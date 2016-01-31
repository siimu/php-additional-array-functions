<?php
  
function array_group_by(array $values , $condition){
    $ret = [];
    if(is_callable($condition)){
        $func = $condition;
        foreach($values as $k => $v){
            $idx = $func($v);
            !isset($ret[$idx]) and $ret[$idx] = [];
            $ret[$idx][$k] = $v;
        }
        return $ret;
    }
    foreach($values as $k => $v){
        $idx = $v[$condition];
        !isset($ret[$idx]) and $ret[$idx] = [];
        $ret[$idx][$k] = $v;
    }
    return $ret;
}