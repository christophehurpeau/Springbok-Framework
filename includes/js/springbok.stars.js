$$.stars={
	create:function(nbStars,value,split){
		var div=$('<div class="stars"/>');
		for(var i=1;i<=nbStars;i++) div.append('<input name="rating" type="radio" value="'+i+'"/>');
		if(value) div.find('input:radio[value='+value+']').prop('checked',true);
		return this.transform(div);
	},
	transform:function(selector,split){
		return $(selector).each(function(){
			var $list = $('<div></div>');
			$(this)
				.find('input:radio')
				.each(function(i){
					var rating = $(this).parent().text();
					var $item = $('<a href="#"></a>')
						.text(rating);
					if(rating) $item.attr('title',rating);
					if(split)
						$item.addClass('rating-'+(i%split));
					$list.append($$.stars.addHandlers($item,i+1));
					if($(this).is(':checked')) $item.prevAll().andSelf().addClass('rating');
				})
				.hide()
				.parent().append($list);
		});
	},
	addHandlers:function(item,value){
		return $(item).click(function(e){
			var $star = $(this);
			var $allLinks = $(this).parent();
			// Set the radio button value
			$allLinks
			  .parent()
			  .find('input:radio[value=' + value + ']')
			  .prop('checked', true);
			// Set the ratings
			$allLinks.children().removeClass('rating');
			$star.prevAll().andSelf().addClass('rating');
			// prevent default link click
			e.preventDefault();
			return false;
		})
		.hover(function(){
			$(this).prevAll().andSelf().addClass('rating-hover');
		 },function(){
			$(this).siblings().andSelf().removeClass('rating-hover');
		})
		
	},
	
	
	spans:function(val,max){
		max=!max ? 5 : Number(max);
		val=!val ? 0 : Number(val);
		return '<span class="stars">'+'<span class="rating"></span>'.sbRepeat(val)+'<span></span>'.sbRepeat(max-val)+'</span>';
	}
};
