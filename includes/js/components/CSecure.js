(S.CSecure={
	connected:null,
	
	init:function(){
		this.storage=new S.StoredConfig('S.CSecure');
	},
	
	isConnected:function(){
		if(this.connected===null){
			this.connected=this.storage.get('connected');
		}
		return !!this.connected;
	},
	
	checkAccess:function(){
		if(!this.isConnected()){
			S.app.load('/site/login');
			return false;
		}
		return true;
	}
}).init();
