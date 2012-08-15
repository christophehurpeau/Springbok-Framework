(S.CSecure={
	init:function(){
		this.storage=new S.StoredConfig('S.CSecure');
	},
	
	isConnected:function(){
		return !!this.connected();
	},
	connected:function(){
		if(this._connected===undefined){
			this._connected=this.storage.get('connected');
			this._token=this.storage.get('token');
		}
		return this._connected;
	},
	
	checkAccess:function(){
		if(!this.isConnected()){
			App.load('/site/login');
			return false;
		}
		return true;
	},
	reconnect:function(){
		this.setConnected(false);
		this.checkAccess();
		throw new S.Controller.Stop();
	},
	
	setConnected:function(userId,token){
		this.storage.set('connected',this._connected=userId);
		this.storage.set('token',this._token=token);
	}
}).init();
