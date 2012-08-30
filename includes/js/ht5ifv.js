includeCore('libs/ht5ifv-0.9.7');
/*
include Lib ('ht5ifv-0.9.8');

(function(){
	var $d = $.ht5ifv('defaults');
	$d.callbacks=function($this,$type){
		if($type === 'invalid'){
			if(!$this.data('invalidMessage'))
				$this.data('invalidMessage',
					$('<div class="message error bold" style="display:none;opacity:0"/>')
						.text($this.data('err-msg')||i18nc['This field is required']).animate({opacity:1,height:'show'},'fast')
				);
		}else if($type === 'valid'){
			if($this.data('invalidMessage'))
				$this.data('invalidMessage').animate({opacity:0,height:'hide'},'slow');
		}
	};
})();
*/