String.prototype.lcFirst=function(){return this.charAt(0).toLowerCase()+this.substr(1);};
String.prototype.ucFirst=function(){return this.charAt(0).toUpperCase()+this.substr(1);};
String.prototype.startsWith=function(str){return this.indexOf(str)===0;};
String.prototype.endsWith=function(str){return this.match(str+"$")==str;};
String.prototype.trim=function(pattern){return this.ltrim(pattern).rtrim(pattern);};
String.prototype.ltrim=function(pattern){if(pattern===undefined) pattern='\\s+'; return this.replace(new RegExp('^'+pattern,'g'),'');};
String.prototype.rtrim=function(pattern){if(pattern===undefined) pattern='\\s+'; return this.replace(new RegExp(pattern+'$','g'),'');};
String.prototype.repeat=function(m){return new Array(m + 1).join(this)};
String.prototype.removeSpecialChars=function(){
	var t=this;
	$.each([
		[/æ|ǽ/g,'ae'],[/œ/g,'oe'],[/Ä|À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ/g,'A'],[/ä|à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/g,'a'],
		[/Ç|Ć|Ĉ|Ċ|Č/g,'C'],[/ç|ć|ĉ|ċ|č/g,'c'],
		[/Ð|Ď|Đ/g,'D'],[/ð|ď|đ/g,'d'],
		[/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě/g,'E'],[/è|é|ê|ë|ē|ĕ|ė|ę|ě/g,'e'],
		[/Ĝ|Ğ|Ġ|Ģ/g,'G'],[/ĝ|ğ|ġ|ģ/g,'g'],
		[/Ĥ|Ħ/g,'H'],[/ĥ|ħ/g,'h'],
		[/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/g,'I'],[/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/g,'i'],
		[/Ĵ/g,'J'],[/ĵ/g,'j'],[/Ķ/g,'K'],[/ķ/g,'k'],
		[/Ĺ|Ļ|Ľ|Ŀ|Ł/g,'L'],[/ĺ|ļ|ľ|ŀ|ł/g,'l'],
		[/Ñ|Ń|Ņ|Ň/g,'N'],[/ñ|ń|ņ|ň|ŉ/g,'n'],
		[/Ö|Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/g,'O'],[/ö|ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º/g,'o'],
		[/Ŕ|Ŗ|Ř/g,'R'],[/ŕ|ŗ|ř/g,'r'],
		[/Ś|Ŝ|Ş|Š/g,'S'],[/ś|ŝ|ş|š|ſ/g,'s'],
		[/Ţ|Ť|Ŧ/g,'T'],[/ţ|ť|ŧ/g,'t'],
		[/Ü|Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/g,'U'],[/ü|ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/g,'u'],
		[/Ý|Ÿ|Ŷ/g,'Y'],[/ý|ÿ|ŷ/g,'y'],
		[/Ŵ/g,'W'],[/ŵ/g,'w'],
		[/Ź|Ż|Ž/g,'Z'],[/ź|ż|ž/g,'z'],
		[/Æ|Ǽ/g,'AE'],[/ß/g,'ss'],[/Ĳ/g,'IJ'],[/ĳ/g,'ij'],[/Œ/g,'OE'],[/ƒ/g,'f'],[/&/g,'et']
	],
	function(i,pattern){
		t=t.replace(pattern[0],pattern[1]);
	});
	return t;
};
String.prototype.slug=function(replacement){
	if(replacement===undefined) replacement='-';
	var returnval=this;
	return returnval.trim().removeSpecialChars()
		.replace(/([^\d\.])\.+([^\d\.]|$)/g,'$1 $2')
		.replace(/[^\w\d\.]/g,' ')
		.trim()
		.replace(/\s+/g,replacement)
		.replace(new RegExp('^'+RegExp.escape(replacement)+'+|'+RegExp.escape(replacement)+'+$'),'');
};

/* http://phpjs.org/functions/strip_tags:535 */
String.prototype.stripTags=function(allowed){
	var input=this;
	allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
	var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
    	commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
	return input.replace(commentsAndPhpTags, '').replace(tags,function($0,$1){
		return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
	});
};

String.prototype.wordsCount=function(){
	var t=this.replace(/\.\.\./g, ' ') // convert ellipses to spaces
		.replace(/[0-9.(),;:!?%#$?\'\"_+=\\\/-]*/g,'') // remove numbers and punctuation
		;
	var wordArray = t.match(/[\w\u2019\'\-]+/g); //u2019 == &rsquo;
	if(wordArray) return wordArray.length;
	return 0;
};
String.prototype.htmlWordsCount=function(){
	return this.replace(/<.[^<>]*?>/g, ' ').replace(/&nbsp;|&#160;/gi, ' ') // remove html tags and space chars
		.replace(/(\w+)(&.+?;)+(\w+)/, "$1$3").replace(/&.+?;/g, ' ').wordsCount();
};