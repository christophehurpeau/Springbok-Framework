(S.CSecure={
	_connected:null,
	
	init:function(){
		this.storage=new S.StoredConfig('S.CSecure');
	},
	
	isConnected:function(){
		return !!this.connected();
	},
	connected:function(){
		if(this._connected===null)
			this._connected=this.storage.get('connected');
		return this._connected;
	},
	
	checkAccess:function(){
		if(!this.isConnected()){
			App.load('/site/login');
			return false;
		}
		return true;
	}
}).init();
