adapters.indexedDb=function(name,config,callback){
	var t=this;
	t.name=name; t.config=config;
};

adapters.indexedDb.indexedDB=window.indexedDB || window.webkitIndexedDB || window.mozIndexedDB || window.msIndexedDB;

/*IDBTransaction:window.IDBTransaction || window.webkitIDBTransaction,
	IDBKeyRange:window.IDBKeyRange || window.webkitIDBKeyRange,
	*/
	
adapters.indexedDb.isAvailable=function(){
	return this.indexedDb;
};
