<?php
// date_default_timezone_set('UTC');
require '../components/CLang.php';

function _t($string,$alt=null){$r=CLang::translate($string,'a'); return $r!==false ? $r : ($alt===null?$string:$alt);}
function tf($string){$args=func_get_args(); $r=CLang::translate(array_shift($args),'a'); return vsprintf($r!==false ? $r : $string,$args);}

function _tC($string){$r=CLangCore::translate($string); return $r!==null ? $r : $string;}
function _tF($modelName,$fieldName='',$alt=null){$r=CLang::translate($modelName.':'.$fieldName,'f'); return $r!==false ? $r : ($alt===null?$fieldName:$alt);}

function _t_p($string,$count){$r=CLang::translate($string,App::getLocale()->isPlural((int)$count)?'s':'p'); return $r!==false ? $r : $string;}

function _tR($string){return CRoute::translate($string,CLang::get());}

function _sp($count,$singular,$plural){ return App::getLocale()->isPlural((int)$count)?$plural:$singular; }
