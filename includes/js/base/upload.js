(function(){
	var upload=S.upload=function(form){
		return new S.upload.Class(form);
	},uploadClass=function(form){this.form=form;},NextFileId=1;
	
	upload.FileUploaded=function(id,result){
		upload.Files[id].fileUploaded(result);
	};
	
	S.extendsPrototype(uploadClass,{
		addFile:function(id){
			upload.Files[id]=this;
		},
		fileAdded:function(){
			this.addFile(upload.NextFileId++);
			this.startUpload();
		},
		fileUploaded:function(id){
			
		}
	});
})();