<?php
// date_default_timezone_set('UTC');
include CORE.'components/CLang.php';

function _t($string){$r=CLang::translate($string,'a'); return $r!==false ? $r : $string;}
function tf($string){$args=func_get_args(); $r=CLang::translate(array_shift($args),'a'); return vsprintf($r!==false ? $r : $string,$args);}

function _tC($string){$r=CLangCore::translate($string); return $r!==null ? $r : $string;}
function _tF($modelName,$fieldName=''){$r=CLang::translate($modelName.':'.$fieldName,'f'); return $r!==false ? $r : $fieldName;}

function _t_p($string,$count){$r=CLang::translate($string,$count===1||$count==='1'?'s':'p'); return $r!==false ? $r : $string;}

function _tR($string){return CRoute::translate($string,CLang::get());}
