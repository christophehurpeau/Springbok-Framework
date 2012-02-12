$$.videos={
	getYoutubeLinks:function(str){
		// short youtube video : "http://youtu.be/id"
		//function getYoutubeVideos(){
			//var reg = new RegExp("(\\s?)((http|https)://[^\\s<]+[^\\s<\.)])", "gim");
			//res= str.match(/(\\s?)(?:http:\/\/(www\.)?)((?:youtube.com\/watch\?)(?=.*v=\w+)|((?:youtu.be\/)(\w+)))[^\\s<]*[^\\s<\.)]?/gim);
			//res= str.match(/(\\s?)(http:\/\/(www\.)?youtube.com\/watch\?(?=.*v=\w+))[^\\s<]*[^\\s<\.)]/gim);
			res= str.match(/(?:http:\/\/youtu.be\/)([\w-]+)(?:\S+)?/gim);
			return res;
		//}
	}
};