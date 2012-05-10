S.HPagination=function(url,content,pager,resultPerPage,options){
	this.url=url;
	this.$content=content;
	this.$pager=pager;
	
	this.totalPages=pager.find('li.page:last').text();
	this.options=S.extendsObj({nbBefore:3,nbAfter:3,hidden:true,withText:false},options);
};

S.HPagination.prototype.updatePager=function(page){
	var t=this,options=t.options,pager=t.$pager.empty();
	if(page==1){
		if(options.hidden) pager.append('<li class="first hidden"><a href="javascript:;">&lt;&lt;'+(options.withText?' '+i18nc.Start:'')+'</a></li>'
				+'<li class="previous hidden"><a href="javascript:;">&lt;'+(options.withText?' '+i18nc.Previous:'')+'</a></li>');
		}else{
			pager.append($('<li class="first"/>').html($('<a/>').click(function(){t.load(1)}).html('&lt;&lt;'.($withText?' '+i18nc.Start:''))),
				$('<li class="previous"/>').html($('<a/>').click(function(){t.load(page-1)}).html('&lt;&lt;'.($withText?' '+i18nc.Previous:'')))
			);
		}
		var start=page-options.nbBefore; 
		if(start <= 1) start=1;
		else{
			var i=1; pager.append($('<li class="page">').html($('<a/>').click(function(){t.load(i)}).html(i)));
			$i=ceil(($start)/2);
			if($i != 1){
				if($i != 2) $str.='<li>&nbsp</li>';
				$str.='<li class="page"><a '.$callback($i).'>'.$i.'</a></li>';
				if($i+1 != $start) $str.='<li>&nbsp</li>';
			}
		}
		
		$end=page+$nbAfter; if($end > $totalPages) $end=$totalPages;
		
		$i=$start;
		while($i<=$end){
			$str.='<li class="page'; if($i==page) $str.=' selected'; $str.='"><a '.$callback($i).'>'.$i++.'</a></li>';
		}
		
		if($end < $totalPages){
			$i=floor(($totalPages-$i)/2+$end);
			if($i != $end){
				if($end+1 != $i) $str.='<li>&nbsp</li>';
				$str.='<li class="page'; $str.='"><a '.$callback($i).'>'.$i.'</a></li>';
			}
			if($i!=$totalPages-1) $str.='<li>&nbsp</li>';
			$i=$totalPages; $str.='<li class="page'; $str.='"><a '.$callback($i).'>'.$i.'</a></li>';
		}
		
		if(page>=$totalPages){
			if($hidden) $str.='<li class="next hidden"><a href="javascript:;">'.($withText?_tC('Next').' ':'').'&gt;</a></li>'
				.'<li class="last hidden"><a href="javascript:;">'.($withText?_tC('End').' ':'').'&gt;&gt;</a></li>';
		}else{
			$str.='<li class="next"><a '.$callback(page+1).'>'.($withText?_tC('Next').' ':'').'&gt;</a></li>'
				.'<li class="last"><a '.$callback($totalPages).'>'.($withText?_tC('End').' ':'').'&gt;&gt;</a></li>';
		}
		return $str.'</ul>';
};

HPagination.prototype.load=function(page){
	this.updatePager(page);
};