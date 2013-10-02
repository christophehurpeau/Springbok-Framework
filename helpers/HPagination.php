<?php
class HPagination{
	/*
	 * <code>
	 * $totalPages=(int)ceil((double)$nbRows / NB_RESULTS_PER_PAGE);
	 * 	$urlbase='search.php?q='.urlencode($_GET['q']).'&o='.urlencode($_GET['o']).'&b='.$p.'&p=';
	 * 	
	 * 	if($totalPages > 1) echo $pagination='<div class="pager">'.HPagination::createPager($p,$totalPages,function($i) use(&$urlbase){return ' href="'.$urlbase.$i.'"';},
	 * 		(isset($_GET['b'])?($_GET['b']>$p?3:2):2),(isset($_GET['b'])?($_GET['b']<$p?3:2):2)).'</div>';
	 * 
	 * if($totalPages > 1) echo $pagination='<div class="pager">'.HPagination::createPager($p,$totalPages,function($i) use(&$urlbase){return ' href="'.$urlbase.$i.'"';},
	 * 		(isset($_GET['b']) && $p<1000?($_GET['b']>$p?3:2):2)+($p<=20?1:0),(isset($_GET['b']) && $p<1000?($_GET['b']<$p?3:2):2)+($p<=20?1:0)).'</div>';
	 * </code>
	 */
	/**
	 * Create a pager
	 * 
	 * @param int
	 * @param int
	 * @param function
	 * @param int
	 * @param int
	 * @param bool
	 * @param string|null
	 * @param bool
	 * @return string
	 */
	public static function createPager($page,$totalPages,$callback,$nbBefore=3,$nbAfter=3,$disabled=true,$withText=null,$tiny=false){
		//if($page>1) HMeta::prev($callback($page-1));
		//if($page<$totalPages) HMeta::next($callback($page+1));
		
		$str='<ul class="pager">';
		if($withText!==null){
			if($page==1){
				if($disabled) $str.='<li class="first disabled"><a href="javascript:;">&lt;&lt;'.($withText?' '._tC('Start'):'').'</a></li>'
					.'<li class="previous disabled"><a href="javascript:;">&lt;'.($withText?' '._tC('Previous'):'').'</a></li>';
			}else{
				$str.='<li class="first"><a '.$callback(1).'>&lt;&lt;'.($withText?' '._tC('Start'):'').'</a></li>'
					.'<li class="previous"><a '.$callback($page-1).'>&lt;'.($withText?' '._tC('Previous'):'').'</a></li>';
			}
		}
		$start=$page-$nbBefore; 
		if($start <= 1) $start=1;
		else{
			$i=1; $str.='<li class="page"><a '.$callback($i).'>'.$i.'</a></li>';
			$i=ceil(($start)/2);
			if($i != 1){
				if($tiny===false){
					if($i != 2) $str.='<li>&nbsp;</li>';
					$str.='<li class="page"><a '.$callback($i).'>'.$i.'</a></li>';
				}
				if($i+1 != $start) $str.='<li>&nbsp;</li>';
			}
		}
		
		$end=$page+$nbAfter; if($end > $totalPages) $end=$totalPages;
		
		$i=$start;
		while($i<=$end){
			$str.='<li class="page'; if($i==$page) $str.=' selected'; $str.='"><a '.$callback($i).'>'.$i++.'</a></li>';
		}
		
		if($end < $totalPages){
			if($tiny===false || $i===$start){
				$i=floor(($totalPages-$i)/2+$end);
				if($i != $end){
					if($end+1 != $i) $str.='<li>&nbsp;</li>';
					$str.='<li class="page'; $str.='"><a '.$callback($i).'>'.$i.'</a></li>';
				}
			}
			if($i!=$totalPages-1) $str.='<li>&nbsp;</li>';
			$i=$totalPages; $str.='<li class="page'; $str.='"><a '.$callback($i).'>'.$i.'</a></li>';
		}

		if($withText!==null){
			if($page>=$totalPages){
				if($disabled) $str.='<li class="next disabled"><a href="javascript:;">'.($withText?_tC('Next').' ':'').'&gt;</a></li>'
					.'<li class="last disabled"><a href="javascript:;">'.($withText?_tC('End').' ':'').'&gt;&gt;</a></li>';
			}else{
				$str.='<li class="next"><a '.$callback($page+1).'>'.($withText?_tC('Next').' ':'').'&gt;</a></li>'
					.'<li class="last"><a '.$callback($totalPages).'>'.($withText?_tC('End').' ':'').'&gt;&gt;</a></li>';
			}
		}
		return $str.'</ul>';
	}
	
	/**
	 * Create a pager with letters
	 * 
	 * @param string
	 * @param array
	 * @param function
	 * @return string
	 */
	public static function createPagerLetters($page,$avalaibleLetters,$callback){//ob_clean();debugVar($avalaibleLetters);exit;
		$str='<ul class="pager">'; $i=65;
		while($i<91){
			$letter=chr($i++); $available=in_array($letter,$avalaibleLetters);
			$str.='<li class="page'; if(!$available) $str.=' disabled'; elseif($letter==$page) $str.=' selected'; $str.='"><a '.($available?$callback($letter):' href="javascript:;"').'>'.$letter.'</a></li>';
		}
		return $str.'</ul>';
	}
	
	/**
	 * Create a simple pager
	 * 
	 * @param int
	 * @param int
	 * @param string
	 */
	public static function simple_($page,$totalPages,$pageName='page'){
		if($totalPages>1) 
			return $pager='<div class="pager">'.self::createPager($page,$totalPages,
			function($page) use($pageName){
				return ' href="'.HHtml::url(CRoute::getAll()).($page<2 ? '' : '?'.$pageName.'='.$page).'"';
			}).'</div>';
		return '';
	}

	/**
	 * Create a simple pager
	 * 
	 * @param CValidation
	 * @param string
	 */
	public static function simple($pagination,$pageName='page'){
		return self::simple_($pagination->getPage(),$pagination->getTotalPages(),$pageName);
	}
	
	/**
	 * Create a simple ajax pager
	 * 
	 * @param CValidation
	 * @param function
	 * @param string|function
	 */
	public static function simpleAjax($pagination,$callbackName,$callbackPage='#'){
		if($pagination->hasPager()) 
			return $pager='<div class="pager">'.self::createPager($pagination->getPage(),$pagination->getTotalPages(),
			function($page) use(&$callbackName,&$callbackPage){
				return ' href="'.(is_string($callbackPage)?$callbackPage:$callbackPage($page)).'" onclick="return '.$callbackName.'('.$page.')"';
			}).'</div>';
		return '';
	}
	
	/**
	 * @param CPagination
	 * @param array|null if null, ask pagination->getAvailableLetters()
	 * @return string
	 */
	public static function simpleLetters($pagination,$availableLetters=null){
		if($availableLetters===null) $availableLetters=$pagination->getAvailableLetters();
		return $pager='<div class="pager">'.self::createPagerLetters($pagination->getPage(),$availableLetters,function($page){
			return ' href="'.HPagination::callbackCreateUrl($page).'"';
		}).'</div>';
	}
	
	/**
	 * @param int
	 * @return string
	 */
	public static function callbackCreateUrl($page){
		return HHtml::url(CRoute::getAll()).($page<2 ? '' : '?page='.$page);
	}
}