$.fn.sSlideTo=function(content,callback){
	var t=$(this),tW=t.width(),parent=t.parent().css({'position':'relative','overflow-x':'hidden'}),parentW=parent.width(),
		tHeight=t.height(),heightPlusParent=parent.height()-tHeight,tOldPositioning=t.css('position');
	
	t.css({position:'absolute',width:tW});
	var newContent=t.clone(true,true).html(content).css({left:parentW}),newContentHeight;
	t.css({height:tHeight});
	
	parent.append(newContent).css('height',((newContentHeight=newContent.height()) > tHeight ? newContentHeight : tHeight) + heightPlusParent);
	
	t.add(newContent).animate({left: "-="+parentW},function(){
		t.remove();
		parent.add(newContent).removeAttr('style');
		callback();
	});
	
	return newContent;
};