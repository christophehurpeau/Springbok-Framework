(S.CDB={
	indexedDB:window.indexedDB || window.webkitIndexedDB || window.mozIndexedDB || window.msIndexedDB,
	/*IDBTransaction:window.IDBTransaction || window.webkitIDBTransaction,
	IDBKeyRange:window.IDBKeyRange || window.webkitIDBKeyRange,
	*/
	config:includeJsAppConfig('databases'),
	dbs:{},
	
	init:function(){
		if(!this.indexedDB) throw new FatalError('IndexedDB doesn\'t work on your browser. Please use Firefox or Chrome.');
	},
	
	get:function(dbName,callback){
		if(this.dbs[dbName]) callback(this.dbs[dbName]);
		else{
			if(S.CSecure){
				if(!S.CSecure.isConnected()) throw new FatalError('CDB.mustBeConnected');
				dbName+=S.CSecure.connected();
			}
			new Pouch('idb://'+dbName,{},function(err,db){
				console.log(err,db);
				if(err) throw new FatalError(err);
				callback((this.dbs[dbName]=db));
			});
		}
	},
}).init();


/*var r=this.request=indexedDB.open("MyTestDatabase",1);
r.onblocked=function(){ throw new FatalError('Please close all other tabs with this site open!') };
r.onupgradeneeded=function(event){
	var db = event.target.result;
	// Create an objectStore to hold information about our customers. We're
	// going to use "ssn" as our key path because it's guaranteed to be
	// unique.
	var objectStore = db.createObjectStore("customers", { keyPath: "ssn" });
	
	// Create an index to search customers by name. We may have duplicates
	// so we can't use a unique index.
	objectStore.createIndex("name", "name", { unique: false });
	
	// Create an index to search customers by email. We want to ensure that
	// no two customers have the same email, so use a unique index.
	objectStore.createIndex("email", "email", { unique: true });
	S.CDB.useDatabase(db);
};
r.onerror=function(event){alert("Database error: " + event.target.errorCode);throw new FatalError('You didn\'t allow us to use IndexedDB') };
r.onsuccess=function(event){ var db=event.target.result; S.CDB.useDatabase(db); return; };*/