/** http://kevin.vanzonneveld.net/techblog/article/create_short_ids_with_php_like_youtube_or_tinyurl/
 *  Javascript AlphabeticID class
 *  (based on a script by Kevin van Zonneveld <kevin@vanzonneveld.net>)
 *
 *  Author: Even Simon <even.simon@gmail.com>
 *
 *  Description: Translates a numeric identifier into a short string and backwords.
 *
 *  Usage:
 *	var str = AlphabeticID.encode(9007199254740989); // str = 'fE2XnNGpF'
 *	var id = AlphabeticID.decode('fE2XnNGpF'); // id = 9007199254740989;
 **/
 
var AlphabeticID = {
  index:'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
 
  /**
   *  <a href="http://twitter.com/function">@function</a> AlphabeticID.encode
   *  <a href="http://twitter.com/description">@description</a> Encode a number into short string
   *  <a href="http://twitter.com/param">@param</a> integer
   *  <a href="http://twitter.com/return">@return</a> string
   **/
  encode:function(_number){
	if('undefined' == typeof _number){
	  return null;
	}
	else if('number' != typeof(_number)){
	  throw new Error('Wrong parameter type');
	}
 
	var ret = '';
 
	for(var i=Math.floor(Math.log(parseInt(_number))/Math.log(AlphabeticID.index.length));i>=0;i--){
	  ret = ret + AlphabeticID.index.substr((Math.floor(parseInt(_number) / AlphabeticID.bcpow(AlphabeticID.index.length, i)) % AlphabeticID.index.length),1);
	}
 
	return ret.reverse();
  },
 
  /**
   *  <a href="http://twitter.com/function">@function</a> AlphabeticID.decode
   *  <a href="http://twitter.com/description">@description</a> Decode a short string and return number
   *  <a href="http://twitter.com/param">@param</a> string
   *  <a href="http://twitter.com/return">@return</a> integer
   **/
  decode:function(_string){
	if('undefined' == typeof _string){
	  return null;
	}
	else if('string' != typeof _string){
	  throw new Error('Wrong parameter type');
	}
 
	var str = _string.reverse();
	var ret = 0;
 
	for(var i=0;i<=(str.length - 1);i++){
	  ret = ret + AlphabeticID.index.indexOf(str.substr(i,1)) * (AlphabeticID.bcpow(AlphabeticID.index.length, (str.length - 1) - i));
	}
 
	return ret;
  },
 
  /**
   *  <a href="http://twitter.com/function">@function</a> AlphabeticID.bcpow
   *  <a href="http://twitter.com/description">@description</a> Raise _a to the power _b
   *  <a href="http://twitter.com/param">@param</a> float _a
   *  <a href="http://twitter.com/param">@param</a> integer _b
   *  <a href="http://twitter.com/return">@return</a> string
   **/
  bcpow:function(_a, _b){
	return Math.floor(Math.pow(parseFloat(_a), parseInt(_b)));
  }
};
 
/**
 *  <a href="http://twitter.com/function">@function</a> String.reverse
 *  <a href="http://twitter.com/description">@description</a> Reverse a string
 *  <a href="http://twitter.com/return">@return</a> string
 **/
String.prototype.reverse = function(){
  return this.split('').reverse().join('');
};