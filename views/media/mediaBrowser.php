<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use schallschlucker\simplecms\models\SimpleMediaCategory;
use schallschlucker\simplecms\assets\FancytreeAsset;
use schallschlucker\simplecms\assets\SimpleCmsAsset;
use schallschlucker\simplecms\controllers\mediacontroller\MediaController;

/* @var $this yii\web\View */
FancytreeAsset::register ( $this );
SimpleCmsAsset::register ( $this );
?>
<?php
$deleteMediaItemUrl = Url::toRoute(['media/delete-media-item-json','mediaItemId' => '123']);
$moveMediaItemUrl = Url::toRoute(['media/move-media-item-json']);
$deleteMediaVariationItemUrl = Url::toRoute(['media/delete-media-variation-item-json','mediaVariationItemId' => '123']);
$createCategoryItemUrl = Url::toRoute(['media/create-category-item-json']);
$renameCategoryItemUrl = Url::toRoute(['media/rename-content-category-json']);
$jsonUrl = Url::toRoute(['media/category-tree-json','mediaType' => $mediatype, 'activeCategoryId' => $activeCategoryId]);
$mediaItemsUrl = Url::toRoute(['media/media-for-category','categoryId' => '123']);
$mediaUploadUrl = Url::toRoute(['media/upload','targetCategoryId' => '123','mediaType' => $mediatype]);
$mediaCategoryIdsNotToDelete = json_encode([''.MediaController::$MEDIA_AUDIO_BASE_CATEGORY_ID,''.MediaController::$MEDIA_VIDEO_BASE_CATEGORY_ID,''.MediaController::$MEDIA_IMAGE_BASE_CATEGORY_ID,''.MediaController::$ROOT_MEDIA_CATEGORY_ID]);
$deleteFolderUrl = Url::toRoute(['media/delete-content-category-item-json','contentCategoryId' => '123']);
$javaScript = <<<JS
	var protectedCategoryIds = $mediaCategoryIdsNotToDelete; 

	function deleteMediaItem(mediaItemId, variationId,htmlId){
		if(confirm('Do you realy want to delete this media item?')){
			if(variationId == '' || variationId == undefined || variationId == null){
				jQuery.ajax({
					url: '$deleteMediaItemUrl'.replace('123',mediaItemId),
					data : {},
					dataType: 'json',
					success: function(result){
						console.log(result);
						if(result[0].success == true){
							$('#'+htmlId).hide();
						} else {
							alert(result[0].message);
						}
					}
				});
			} else {
				jQuery.ajax({
					url: '$deleteMediaVariationItemUrl'.replace('123',variationId),
					data : {},
					dataType: 'json',
					success: function(result){
						console.log(result);
						if(result[0].success == true){
							alert('variation deleted successfully');
						} else {
							alert(result[0].message);
						}
					}
				});
			}
		}
	}
	
	function onClose(imgUrl,width,height){
		if(width == 0 || height == 0){
			var result='<img src="'+imgUrl+'">';
		} else {
			var result='<img src="'+imgUrl+'" style="width:'+width+'px;heigth:'+height+'px;">';
		}
		var element = window.opener.CKEDITOR.dom.element.createFromHtml( result );
		var CKEDITOR   = window.opener.CKEDITOR;
		for ( var i in CKEDITOR.instances ){
		   var currentInstance = i;
		   break;
		}
		var oEditor = window.opener.CKEDITOR.instances[currentInstance];
		oEditor.insertElement(element);
		window.close();
	}

	function showMediaForCategory(categoryId){
		var mediaItemUrl = '$mediaItemsUrl';
		mediaItemUrl = mediaItemUrl.replace('123',categoryId);
		$('#mediaItems').load(mediaItemUrl);
	}
	
	function renameFolder(){
		var node = $("#categoryTree").fancytree("getActiveNode");
    	if( node ){
    		parentFolderName = node.title;
    		parentFolderId = node.key;
    		var foldername = prompt("Please enter the new name for the folder\\n(only characters a-z/A-Z, numbers 0-9, blanks, '-' and '_' are allowed)", "");
    		foldername = foldername.replace(/[^a-zA-Z0-9_\s\-]/g,'');
    		foldername = foldername.trim();
    		if(foldername == ''){
    			alert('New folder name must not be blank. Only characters, numbers, spaces, "_" and "-" are allowerd');
    			return;
    		}
        	//perform ajax call to create new folder and add item to tree
        	jQuery.ajax({
				url: '$renameCategoryItemUrl',
				data : {
					categoryItemId:node.key, 
					newName:foldername
				},
				dataType: 'json',
				success: function(result){
					console.log(result);
					if(result[0].success == true){
			        	node.setTitle(foldername);
			        	//sort alphabetically 
						node.sortChildren();
						$("#categoryTree").fancytree("getTree").getNodeByKey(newKey).setActive();
					} else {
						alert(result[0].message);
					}
				}
			});
      	} else {
        	alert("Select the folder to rename");
      	}
	}
	
	function createNewFolderBelowSelected(){
		var node = $("#categoryTree").fancytree("getActiveNode");
    	if( node ){
    		parentFolderName = node.title;
    		parentFolderId = node.key;
    		var foldername = prompt("Please enter the name for the new folder", "");
    		foldername = foldername.trim();
    		if(foldername == ''){
    			alert('Folder name must not be blank');
    			return;
    		}
        	//perform ajax call to create new folder and add item to tree
        	jQuery.ajax({
				url: '$createCategoryItemUrl',
				data : {
					parentCategoryId: parentFolderId, 
					name: foldername
				},
				dataType: 'json',
				success: function(result){
					console.log(result);
					if(result[0].success == true){
			        	newKey = ''+result[0].newid; //must be string, otherwise the getNodeByKey does not work
			        	node.addChildren({
			        		title:foldername,
			        		key:newKey,
			        		folder:true,
			        		expanded:true,
						});
			        	//sort alphabetically 
						node.sortChildren();
						$("#categoryTree").fancytree("getTree").getNodeByKey(newKey).setActive();
					} else {
						alert(result[0].message);
					}
				}
			});
      	} else {
        	alert("Select the parent folder where to create the new folder below");
      	}
	}
	
	function deleteSelectedFolder(){
		var node = $("#categoryTree").fancytree("getActiveNode");
    	if( node ){
    		if($.inArray(node.key, protectedCategoryIds) > -1){
    			alert('The folder cannot be deleted, since it is a system folder');
    		} else if(node.children){
    			alert("The folder cannot be deleted, since it contains other subfolders. Delete the subfolders first.");
    		} else if(confirm('Do you realy want to delete the folder "'+node.title+'"?')){
    			folderId = node.key;
    			url = '$deleteFolderUrl'.replace('123',folderId);
        		jQuery.ajax({
					url: url,
					data : {},
					dataType: 'json',
					success: function(result){
						console.log(result);
						if(result[0].success == true){
							parentNode = node.parent
				        	node.remove();
				        	parentNode.setActive();
						} else {
							alert(result[0].message);
						}
					}
				});
        	}
      	} else {
        	alert("Select the folder to delete first");
      	}
	}
	
	function moveMediaItem(mediaItemId,htmlListItemId){
		alert('Drag and drop this button to the desired folder in order to move the item');
	}
	
	function uploadFile(){
		var node = $("#categoryTree").fancytree("getActiveNode");
    	if( node ){
			parentFolderName = node.title;
			parentFolderId = node.key;
			url = '$mediaUploadUrl'.replace('123',parentFolderId);
			$( "#uploadDialog" ).load(url, function( response, status, xhr ) {
				if ( status == "error" ) {
					alert( "An error occrued while loading the mask: "+ msg + xhr.status + " " + xhr.statusText );
				}
			});
			$( "#uploadDialog" ).dialog({
				height: 300,
				width: 500
			});
      	} else {
        	alert("Select the target folder for the upload first");
      	}
	}
	
	$(function(){
		$("#categoryTree").fancytree({
			extensions: ["dnd"],
	    	source: {
	        	url: "$jsonUrl",
				cache: false
	      	},
		    activate: function(event, data) {
	        	var node = data.node;
	       		showMediaForCategory(node.key);
	      	},
	      	init: function(event,data){
				//check if default folder should be opened
				var activeKey = '$activeCategoryId';
				if(activeKey != '')
					$("#categoryTree").fancytree("getTree").getNodeByKey(activeKey).setActive(); 
			},
        	dnd: {
				preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
				preventRecursiveMoves: true, // Prevent dropping nodes on own descendants
				autoExpandMS: 400,
				draggable: {
					scroll: false,
					revert: "invalid"
				},
				dragStart: function(node, data) {
					return false;
				},
				dragEnter: function(node, data) {
        			if(node.key == 0 || node.key == node.tree.getActiveNode().key)
        				return false;
					return ["over"];
				},
				dragDrop: function(node, data) {
					if( !data.otherNode ){
						if(confirm("Do you realy want to move this item to the folder '"+node.title+"'?")){
		        			jQuery.ajax({
								url: '$moveMediaItemUrl',
								data : {
									mediaItemId:data.draggable.options.customId,
									targetCategoryId:node.key
								},
								dataType: 'json',
								success: function(result){
									if(result[0].success == true){
										data.draggable.element.get(0).remove();
									} else {
										alert(result[0].message);
									}
								}
        					});
        				}
						return;
					}
				}
			}
		});
	});
JS;

$this->registerJs ( $javaScript, View::POS_END, 'cmsMediaBrowser' );
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
	<base target="_self">
	<style type="text/css">
	#folderBar {
		position: fixed;
		top:10px;
		left:10px;
		width: 250px;
		overflow:auto;
	}
	
	#content {
		position:absolute;
		left:270px;
		top:10px;
		height:100%;
	}
	</style>
</head>
<body>
    <?php $this->beginBody() ?>
    <div class="wrap">
    	<div id="folderBar" class="">
			<div id="categoryTree"></div>
			<div id="mediaTreeFunctions">
				<a class="btn btn-default" onClick="createNewFolderBelowSelected();"><span class="glyphicon glyphicon-asterisk"></span> create new folder</a>
				<a class="btn btn-default" onClick="renameFolder();"><span class="glyphicon glyphicon-pencil"></span> rename folder</a>
				<a class="btn btn-default" onClick="deleteSelectedFolder();"><span class="glyphicon glyphicon-trash"></span> delete folder</a>
				<a class="btn btn-default" onClick="uploadFile();"><span class="glyphicon glyphicon-upload"></span> upload file</a>
			</div>  
		</div>
		<div id="content">
			<div id="mediaItems"><h1>Please select a folder on the right to browse the available media files</h1></div>
		</div>
    </div>

    <footer class="footer">
        <div class="container"></div>
    </footer>

    <div id="uploadDialog" title="Upload"></div>
    
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>