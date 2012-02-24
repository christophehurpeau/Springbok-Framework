S.facebook={
	loaded:false,locale:'en_US',appId:'',
	load:function(){
		if(this.loaded) return;
		S.loadScript('http://connect.facebook.net/'+this.locale+'/all.js');
	},
	onLoaded:function(){
		
	},
	
	connect:function(){
		FB.getLoginStatus(function(response){
			if(response.status === 'connected'){
				var uid = response.authResponse.userID;
				var accessToken = response.authResponse.accessToken;
			}else if (response.status === 'not_authorized'){
				// the user is logged in to Facebook,
				// but not connected to the app
			}else{
				// the user isn't even logged in to Facebook.
			}
		});
	}
};
window.fbAsyncInit = function(){ S.facebook.onLoaded() };