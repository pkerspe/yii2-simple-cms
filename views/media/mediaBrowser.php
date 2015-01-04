<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use schallschlucker\simplecms\models\SimpleMediaCategory;
use schallschlucker\simplecms\assets\FancytreeAsset;
use schallschlucker\simplecms\assets\SimpleCmsAsset;

/* @var $this yii\web\View */
/* @var $categories SimpleMediaCategory */
FancytreeAsset::register ( $this );
SimpleCmsAsset::register ( $this );
?>
<?php
$jsonUrl = Url::toRoute(['media/category-tree-json']);
$mediaItemsUrl = Url::toRoute(['media/media-for-category','categoryId' => '123']);
$mediaUploadUrl = Url::toRoute(['media/upload','targetCategoryId' => '123']);

$javaScript = <<<JS
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
        	newKey = '123'; //must be string, otherwise the getNodeByKey does not work
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
        	alert("Select the parent folder where to create the new folder below");
      	}
	}
	
	function deleteSelectedFolder(){
		var node = $("#categoryTree").fancytree("getActiveNode");
    	if( node ){
    		if(node.children){
    			alert("The folder cannot be deleted, since it contains other subfolders. Delete the subfolders first.");
    		} else if(confirm('Do you realy want to delete the folder "'+node.title+'"?')){
    			parentFolderName = node.title;
    			parentFolderId = node.key;
        		alert("Deleting item if it does not contain media items: " + node.title);
        	}
      	}else{
        	alert("Select the folder to delete first");
      	}
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
	    	source: {
	        	url: "$jsonUrl",
				cache: false
	      	},
		    activate: function(event, data) {
	        	var node = data.node;
	       		showMediaForCategory(node.key);
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
		overflow:auto;
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
				<a class="btn btn-default" onClick="createNewFolderBelowSelected();"><span class="glyphicon glyphicon-pencil"></span> create new folder</a>
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