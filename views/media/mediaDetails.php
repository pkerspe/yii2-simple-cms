<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use schallschlucker\simplecms\assets\SimpleCmsAsset;
use schallschlucker\simplecms\models\CmsContentMedia;
use schallschlucker\simplecms\models\CmsContentMediaVariation;

/* @var $this yii\web\View */
/* @var $cmsContentMedia CmsContentMedia */
SimpleCmsAsset::register ( $this );

$mediaVariationsDialogUrl = Url::toRoute(['media/media-varation-manager','mediaItemId' => '123']);
$mediaGetItemUrl = Url::toRoute(['media/get-media','mediaItemId' => '123']);
$mediaVariationGetItemUrl = Url::toRoute(['media/get-media','mediaItemId' => '123','variationId' => '456']);

$javaScript = <<<JS
//JS if needed
function openNewMediaVariationDialog(mediaId,mimetype){
	url = '$mediaVariationsDialogUrl'.replace('123',mediaId);
	$( "#mediaVariationDialog" ).load(url, function( response, status, xhr ) {
		if ( status == "error" ) {
			alert( "An error occrued while loading the mask! "+ xhr.statusText );
			return false;
		}
	});
	$( "#mediaVariationDialog" ).dialog({
		height: 400,
		width: 700
	});
}

function setPreviewBg(color){
	$("#mediaPreviewContainer").css('background-color', color);
}

function updatePreview(mediaId,variationId,size,filename){
	if(variationId != undefined){
		url = '$mediaVariationGetItemUrl'.replace('123',mediaId).replace('456',variationId);
	} else {
		url = '$mediaGetItemUrl'.replace('123',mediaId);
	}
	$("#imgWrapper").html('<a href="'+url+'" target="_blank"><img class="mediaImg" src="'+url+'"/></a>');

	if($("#imgWrapper img").outerHeight() > $("#mediaPreviewContainer").outerHeight() || $("#imgWrapper img").outerWidth() > $("#mediaPreviewContainer").outerWidth() ){
		$("#helperBlock").removeClass('helper');
	} else {
		$("#helperBlock").addClass('helper');
	}
				
	$( "#detailsFilename").html('Name: '+filename);
	$( "#detailsFilesize").html('<span class="glyphicon glyphicon-file" title="filesize"></span>'+size+' Bytes ['+Math.round(size/1024*100)/100+' KB / '+Math.round(size/1024/1024*10000)/10000+'  MB]');			
}

function toggleBorder(){
	if($( "#imgWrapper").hasClass('previewImgBorder'))
		$( "#imgWrapper").removeClass('previewImgBorder');
	else
		$( "#imgWrapper").addClass('previewImgBorder');
}
JS;

$this->registerJs ( $javaScript, View::POS_END, 'cmsMediaBrowserDetails' );
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
</head>
<body>
    <?php $this->beginBody() ?>
    <div class="wrap">
		<div class="mediaVariationButtons">
			<div class="btn-group">
			
    <?php
    	echo '<a class="btn btn-default btn-sm" onclick="updatePreview(\''.$cmsContentMedia->id.'\',null,\''.$cmsContentMedia->filesize_bytes.'\',\''.$cmsContentMedia->file_name.'\');" title="media variation with size '.$cmsContentMedia->dimension_width. 'px (width) x '. $cmsContentMedia->dimension_height.'px (height)">'.$cmsContentMedia->dimension_width. 'px x '. $cmsContentMedia->dimension_height.'px <span class="glyphicon glyphicon-info-sign" title="MIME-Type: '.$cmsContentMedia->mime_type." \rFilename: ".$cmsContentMedia->file_name.'"></span></a>';
	    foreach($cmsContentMedia->cmsContentMediaVariations as $cmsContentMediaVariation){
	    	/* @var $cmsContentMediaVariation CmsContentMediaVariation */
	    	echo '<a class="btn btn-default btn-sm" onclick="updatePreview(\''.$cmsContentMedia->id.'\',\''.$cmsContentMediaVariation->id.'\',\''.$cmsContentMediaVariation->filesize_bytes.'\',\''.$cmsContentMediaVariation->file_name.'\');" title="media variation with size '.$cmsContentMediaVariation->dimension_width. 'px (width) x '. $cmsContentMediaVariation->dimension_height.'px (height)">'.$cmsContentMediaVariation->dimension_width. 'px x '. $cmsContentMediaVariation->dimension_height.'px <span class="glyphicon glyphicon-info-sign" title="MIME-Type: '.$cmsContentMediaVariation->mime_type." \rFilename: ".$cmsContentMediaVariation->file_name."\rSize: ".$cmsContentMediaVariation->filesize_bytes.'"></span></a>'; 
	    }
    ?>
    			<a onClick="openNewMediaVariationDialog('<?php echo $cmsContentMedia->id; ?>','<?php echo $cmsContentMedia->mime_type; ?>');" class="btn btn-success btn-sm">Create variation</a>
    		</div>
    	</div>
    
	    <div class="mediaDetailsImageContainer" id="<?php echo $cmsContentMedia->id; ?>">
			<div class="mediaDisplay" id="mediaPreviewContainer">
				<span id="helperBlock" class="helper"></span>
				<div id="imgWrapper">
					<a href="<?php echo Url::toRoute(['media/get-media','mediaItemId' => $cmsContentMedia->id]) ?>" target="_blank"><img class="mediaImg" src="<?php echo Url::toRoute(['media/get-media','mediaItemId' => $cmsContentMedia->id]) ?>" title="<?php echo $cmsContentMedia->meta_description; ?>"/></a>
				</div>
			</div>
			<div id="mediaPreviewSettings">
				<div class="colorsetting black" title="set backgound color to black" onclick="setPreviewBg('#000');"></div>
				<div class="colorsetting grey" title="set backgound color to grey" onclick="setPreviewBg('#eee');"></div>
				<div class="colorsetting white" title="set backgound color to white" onclick="setPreviewBg('#fff');"></div>
				<div class="bordertoggle" title="toggle dotted border around image" onclick="toggleBorder();"></div>
			</div>
			<div class="metaData">
				<span class="filename" id="detailsFilename">Name: <?php echo $cmsContentMedia->file_name; ?></span>
				<span class="filesize" id="detailsFilesize"><span class="glyphicon glyphicon-file" title="filesize"></span> <?php echo $cmsContentMedia->filesize_bytes; ?> Bytes [<?php echo round($cmsContentMedia->filesize_bytes/1024,2); ?> KB / <?php echo round ($cmsContentMedia->filesize_bytes/1024/1024,4); ?> MB]</span>
			</div>
	    </div>
	</div>
	<div id="mediaVariationDialog" title="Media Variations"></div>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>