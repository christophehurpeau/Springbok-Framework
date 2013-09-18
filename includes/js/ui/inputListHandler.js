includeCore('ui/autocomplete');
includeCore('ui/validation');

(function(){
	var inputListHandler=S.ui.Autocomplete.extend({
		writable:{ minLength:0, },
		select:function(li){ this.input.val(li.text()); this.inputValue.val($(li.data('item')).attr('data-key')); console.log(this.inputValue,$(li.data('item')).attr('data-key')); }
	});
	/*#if DEV*/window.inputListHandlerIncluded=true;/*#/if*/
	$document.on('focus','input[list]',function(e){
		var input=$(this),
			datalist=$('datalist[id="'+input.attr('list')+'"]'),
			handler=new inputListHandler(input.removeAttr('list'),datalist);
		
		handler.inputValue=$(document.getElementById(input.attr('id')+'_hidden'));
		input.click(function(){ $(this).select(); });
		
		// TODO check value
		// TODO on blur|focus|keyup|change check if value correspond to the key selected in the list. If not : validation fail.
		// TODO remove default behavior of check
	});
})();
