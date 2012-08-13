includeCore('springbok.storage');
includeCore('base/arraysort');
includeCore('ui/dialogs');
includeCore('springbok.date');
includeCore('springbok.contextmenu');
includeLib('fancyapps.fancybox/jquery.fancybox.pack');


function Gallery(to,albumLink,imageLink,onSelectImage,imageAttrs){
	var t=this;
	t.$=to;
	t.init=false;
	t.onSelectImage=onSelectImage;
	t.albumLink=albumLink; t.imageLink=imageLink;
	t.imageAttrs=imageAttrs||{};
	t.albumsMap={};
	
	t.config=new S.StoredConfig('S.gallery_'+to.attr('id'));
	t.sortBy=t.config.get('sortBy')||'created';
	t.sortWay=t.config.get('sortWay')||'asc';
	
	t.$.addClass('gallery');
	if(t.fancybox=(to.selector==='')){
		$('body').append($('<div/>').hide().html(t.$));
	}
	t.$ul=$('<ul class="gallery mosaic"/>');
	t.$breadcrumbs=$('<div class="breadcrumbs"/>').html(
		$('<span/>').html($('<a/>').text('/').attr('href','#').click(function(){
			$(this).parent().find('span:first').remove();
			t.selectAlbum(0);
			return false;
		}))
	);
	var id=$.guid++,$fileList=$('<div class="filelist"/>').hide();
	t.$.html('')
		.append($('<div class="floatR mr10"/>')
			.append($('<a href="#" class="action icon folder_add"/>').click(function(){
				S.dialogs.prompt(i18nc.Create,i18nc.Name,i18nc.Create,'',function(newAlbumName){
					$.getJSON(t.albumLink+'/addAlbum',{parentId:t.selectedAlbum,name:newAlbumName},function(data){
						var album={id:data.id,name:newAlbumName,created:new Date().sToSqlDate(),images:0},
							albums=t.albumsMap[t.selectedAlbum].children,
							albumsLength=albums.length;
						albums.push(album);
						t.sort();
						var idxOf=albums.sHas(album);
						t.addAlbum(album,true,idxOf===albumsLength?false:idxOf);
					});
				});
				return false;
			}))
			.append($('<a href="#" class="action icon image_add"/>').attr('id','pickfiles'+id).click(function(){return false;}))
		)
		.append(t.$breadcrumbs).append($fileList)
		.append($('<div/>').addClass('alignRight')
			.append($('<select/>').append($('<option value="created"/>').text(i18nc.Date))
						.append($('<option value="name"/>').text(i18nc.Name))
						.val(t.sortBy)
						.change(function(){t.sortBy=$(this).val();t.sort(true);t.config.set('sortBy',t.sortBy)})
			).append($('<select/>').append($('<option value="asc"/>').text('↓'))
						.append($('<option value="desc"/>').text('↑'))
						.val(t.sortWay)
						.change(function(){t.sortWay=$(this).val();t.sort(true);t.config.set('sortWay',t.sortWay)})
			)
		)
		.append(t.$ul).append('<br class="clear"/>');
	
	var uploader=t.prepareUploader({
		autoUpload:true,browseButton:'pickfiles'+id,dropElement:'EntryText',url:albumLink+'/upload/',
		onFilesAdded:function(files){
			$fileList.show();
			$.each(files,function(i,file){
				$fileList.append($('<div/>').attr('id',file.id).text(file.name + ' (' + plupload.formatSize(file.size) + ')').append(' <b></b>'));
			});
		},
		onUploadProgress:function(file){
			$('#'+file.id+" b").html(file.percent + "%");
		},
		onFileUploaded:function(file,response,resp){
			var $bfile=$('#' + file.id + " b"),error=false;
			if(response.error) error=response.error.message;
			if(error){
				alert(i18nc['Error:']+' '+error);
				$bfile.html("Erreur lors de l'envoi");
			}else{
				$bfile=$bfile.html("Envoi terminé").parent();
				$bfile.delay(1200).animate({opacity:0,height:'hide'},1600,function(){$bfile.remove();if($fileList.is(':empty')) $fileList.slideUp('slow')});
				var image={id:response.id,name:file.name,created:new Date().sToSqlDate()},
					images=t.albumsMap[t.selectedAlbum].images,
					imagesLength=images.length;
				images.push(image);
				t.sort();
				var idxOf=images.sHas(image);
				t.addImage(image,true,idxOf===imagesLength?false:idxOf);
				if(t.selectedAlbum!==0)
					t.albumsMap[t.albumsMap[t.selectedAlbum].parent].children.sbFindBy('id',t.selectedAlbum).images++;
			}
		}
	});
	uploader.bind('BeforeUpload',function(){
		uploader.settings.url=albumLink+'/upload/'+t.selectedAlbum;
	});
};
Gallery.prototype={
	setOnSelectImage:function(callback){this.onSelectImage=callback;},
	load:function(){
		this.selectAlbum(0);
		if(this.fancybox)
			$.fancybox(this.$,{helpers:{overlay:{css:{cursor:'auto'},closeClick:false}}});
	},
	close:function(){
		$.fancybox.close();
	},
	selectAlbum:function(idAlbum){
		if(idAlbum!==0 && this.albumsMap[this.selectedAlbum]['children'].sHas(idAlbum)!==-1) return false;
		if(this.albumsMap[idAlbum]===undefined)
			this.albumsMap[idAlbum]=S.syncJson(this.albumLink,{id:idAlbum});
		this.selectedAlbum=idAlbum;
		this.sort(true);
	},
	sort:function(create){
		var idAlbum=this.selectedAlbum;
		this.albumsMap[idAlbum].images.sbSortBy(this.sortBy,this.sortWay==='asc',this.sortBy==='created'?'stringDates':undefined);
		if(create) this.createListAlbums(this.albumsMap[idAlbum].children,this.albumsMap[idAlbum].images);
	},
	selectImage:function(idImage){
		return this.onSelectImage(idImage,this.albumsMap[this.selectedAlbum].images.sbFindBy('id',idImage));
	},
	createListAlbums:function(albums,images){
		var t=this;
		t.$ul.empty();
		$.each(albums,function(i,album){ t.addAlbum(album); });
		$.each(images,function(i,image){ t.addImage(image); });
	},

	_addItem:function(li,animate,type,idxOf){
		if(animate){
			li.hide().css({'opacity':0,'backgroundColor':'#ddd'});
			if(idxOf===false)this.$ul.append(li);
			else{
				var before=$(this.$ul.children('li.'+type)[idxOf]);
				if(before!==undefined) before.before(li);
				else if(type==='image') this.$ul.append(li);
				else this.$ul.prepend(li);
			}
			li.animate({opacity:1,width:'show',height:'show',borderColor:'#ccc'},1200).delay(3000).animate({backgroundColor:'#fff',borderColor:'#f2f2f2'},300);
		}else this.$ul.append(li);
	},

	/* IMAGE */
	
	addImage:function(image,animate,idxOf){
		var t=this,li=$('<li class="image pointer"/>').attr('title',image.name).click(function(){t.selectImage(image.id);})
			.html(t.image(image.id))
			.append($('<div class="center"/>').text(image.name));
		t._addItem(li,animate,'image',idxOf);
		li.contextmenu({menu:[
			{title:i18nc.Rename,icon:'rename',callbacks:{click:function(){
				S.dialogs.prompt(i18nc.Rename,i18nc['New name ?'],i18nc.Rename,image.name,function(newName){
					image.name=newName;
					li.attr('title',newName).find('div:first').text(newName);
					$.get(t.albumLink+'/renameImage',{id:image.id,newName:newName});
				});
			}}}
		]});
	},
	
	image:function(id){
		return $('<img/>').attr(this.imageAttrs).attr('src',this.imageLink(id));
	},
	

	/* ALBUM */
	
	addAlbum:function(album,animate,idxOf){
		var t=this,li=$('<li class="album pointer"/>').attr('title',album.name).click(function(){var parent=t.selectedAlbum;t.selectAlbum(album.id);t.albumsMap[album.id].id=album.id;t.albumsMap[album.id].parent=parent;t.addBreadcrumb(album)})
			.html(album.imageId?t.image(album.imageId)+' ':$('<img/>').attr(this.imageAttrs).attr('src',imgUrl+'folder-default.png'))
			.append($('<div class="center"/>').text(album.name+' ('+album.images+')'));
		t._addItem(li,animate,'album',idxOf);
		li.contextmenu({menu:[
			{title:i18nc.Rename,icon:'rename',callbacks:{click:function(){
				S.dialogs.prompt(i18nc.Rename,i18nc['New name ?'],i18nc.Rename,album.name,function(newName){
					album.name=newName;
					li.attr('title',newName).find('div:first').text(newName+' ('+album.images+')');
					$.get(t.albumLink+'/renameAlbum',{id:album.id,newName:newName});
				});
			}}}
		]});
	},
	
	
	/* BREADCRUMB */
	
	addBreadcrumb:function(album){
		var t=this,span=$('<span/>').text(' » ');
		span.append($('<a/>').text(album.name).attr('href','#').click(function(){
			span.find('span:first').remove();
			t.selectAlbum(album.id);
			return false;
		}));
		t.$breadcrumbs.find('span:last').append(span);
	},
	
	
	
	prepareUploader:function(options){
		var uploader = new plupload.Uploader({
			runtimes:'html5,flash,silverlight',
			browse_button:options.browseButton,
			drop_element:options.dropElement,
			//max_file_size : '10mb',
			url:options.url,
			//resize : {width : 320, height : 240, quality : 90},
			flash_swf_url :webUrl+'js/plupload/plupload.flash.swf',
			silverlight_xap_url : webUrl+'js/plupload/plupload.silverlight.xap',
			filters : [
				{title : "Images", extensions : "jpg,jpeg,gif,png"}
			]
		});/*
		uploader.bind('Init', function(up, params){
			$('#filelist').html("<div><i>Technique d'upload : " + params.runtime + "</i></div>");
		});*/
		uploader.init();
		uploader.bind('FilesAdded', function(up, files){
			if(options.onFilesAdded) options.onFilesAdded(files);
			if(options.autoUpload) up.start();
		});
		uploader.bind('UploadProgress', function(up, file) {
			if(options.onUploadProgress) options.onUploadProgress(file);
		});
		uploader.bind('FileUploaded',function(up,file,resp){
			var error=false,response;
			if(resp.status != 200) error="Une erreur est survenue";
			else{
				response=jQuery.parseJSON(resp.response);
				if(response['error']!==undefined) error=response.error.message;
			}
			if(error){
				S.dialogs.alert(i18nc.Error,error);
			}else{
				options.onFileUploaded(file,response,resp);
			}
		});
		return uploader;
	}
};
