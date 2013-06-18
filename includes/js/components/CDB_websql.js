adapters.webSQL=function(name,config,callback){
	var t=this;
	t.name=name; t.config=config; t.collections={};
		
};
UObj.extend(adapters.webSQL,{
	isAvailable:function(){
		return !!window.openDatabase;
	},
	init:function(name,config,callback){
		var t=new S.CDB.adapters.webSQL(name,config);
		try{
			t.io=window.openDatabase('CDB','1.0','CDB Offline Database',5*1024*1024);
			callback && callback.call(t,t,'webSQL');
		}catch(err){
			t.trigger('error',err);
			callback && callback.call(t,false,'webSQL');
		}
	}
});
adapters.webSQL.prototype={
	collection:function(name,callback){
		var c=this.collections[name];
		c ? callback(c) : (this.collections[name]=new this.Collection(io,name)).init(callback);
	}
};

adapters.webSQL.Collection=function(io,name){
	this.io=io;this.name=name;
};
adapters.webSQL.Collection.prototype={
	exSQL:function(sqlStatement,args,callback,errorCallback){
		t.io.transaction(function(tx){
			tx.executeSql(sqlStatement,args,callback,errorCallback);
		});
	},
	init:function(callback){
		var t=this;
		t.exSQL('CREATE TABLE IF NOT EXISTS ' + t.name + ' (key TEXT PRIMARY KEY,data TEXT) ',
			function(){
				callback && callback.call(t,'webSQL',true);
			}
		);
	},
	get:function(key,callback){
		var t=this;
		t.exSQL('SELECT * FROM ' + t.name + ' WHERE key=? LIMIT 1',[key],
			function(tx,results){
				if(results.rows.length === 1){
					var record = results.rows.item(0);
					callback && callback.call(t,t.unserialize(record['data']));
				}else{
					callback && callback.call(t,false);
				}
			},function(tx,results){
				t.trigger('error', "Couldn't get webSQL item with key: " + key, key);
				callback && callback.call(store,false);
			}
		);
	},
	set:function(key,item,callback){
		var t=this, value=t.serialize(item);
		t.exSQL('REPLACE INTO '+t.name+' (key,data) VALUES (?,?)',[key,value],
			function(tx,results){
				if(result.rowsAffected === 0){
					store.trigger('error','Failed to set webSQL item',item);
					callback && callback.call(t,false,item);
				}else{
					callback && callback.call(t,true,item);
				}
			}
		);
	},
	remove:function(){
		var t=this;
		t.exSQL('DELETE FROM '+t.name+' WHERE key=?',[key],
			function(tx,results){
				callback && callback.call(t,result.rowsAffected !== 0);
			},function(tx,error){
				t.trigger('error','Failed to delete webSQL item', key);
				callback && callback.call(t,false);
			}
		);
	},
	removeAll:function(){
		var t=this;
		t.exSQL('DELETE FROM '+t.name,[],
			function(tx,results){
				callback && callback.call(store,result.rowsAffected !== 0);
			},function(tx, error){
				store.trigger('error','Failed to remove all webSQL items', error.message);
				callback && callback.call(store,false);
			}
		);
	}
};
