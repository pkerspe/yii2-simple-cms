<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use schallschlucker\simplecms\models\CmsContentMedia;
use schallschlucker\simplecms\models\CmsContentCategory;

/* @var $this yii\web\View */
/* @var $contentMediaArray CmsContentMedia[] */
/* @var $category CmsContentCategory */
if($category != null && $category != false){
?>
<script>
function showDetails(url){
	$( "#detailsDialog" ).load(url);
	$( "#detailsDialog" ).dialog({
		height: 500,
		width: 600
	});
}
</script>
		
<h3>Media for Category '<?php echo $category->displayname ?>'</h3>
<?php
	foreach($contentMediaArray as $cmsContentMedia){
		/* @var $cmsContentMedia CmsContentMedia */
?>
	<div class="mediaBrowserPreviewContainer pull-left" id="media-<?php echo $cmsContentMedia->id; ?>">
			<a href="#" class="btn btn-warning" title="delete this media and all its variations" onclick="deleteMediaItem('<?php echo $cmsContentMedia->id ?>','','media-<?php echo $cmsContentMedia->id; ?>');"><span class="glyphicon glyphicon-trash"></span> Delete</a>
		<div class="btn-group pull-right">
			<a href="#" class="btn btn-default" title="insert primary version" onclick="showDetails('<?php echo Url::toRoute(['media/details','mediaItemId' => $cmsContentMedia->id]) ?>');">Details</a>
			<button type="button" class="btn btn-success" onClick="return onClose('<?php echo Url::toRoute(['media/get-media','mediaItemId' => $cmsContentMedia->id]) ?>',<?php echo (($cmsContentMedia->dimension_width != null)?$cmsContentMedia->dimension_width : 0) ?>,<?php echo (($cmsContentMedia->dimension_height != null)? $cmsContentMedia->dimension_height : 0); ?>)">Insert Media</button>
<?php if(count($cmsContentMedia->cmsContentMediaVariations) > 0) {?>
			<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
				<span class="caret"></span>
				<span class="sr-only">Show variations</span>
			</button>
			<ul class="dropdown-menu" role="menu">
<?php 
	    foreach($cmsContentMedia->cmsContentMediaVariations as $cmsContentMediaVariation){
	    	/* @var $cmsContentMediaVariation CmsContentMediaVariation */
	    	echo "\t\t".'<li><a href="#" onClick="return onClose(\''.Url::toRoute(['media/get-media','mediaItemId' => $cmsContentMedia->id, 'variationId' => $cmsContentMediaVariation->id]).'\','.$cmsContentMedia->dimension_width.','.$cmsContentMedia->dimension_height.')">'.$cmsContentMediaVariation->dimension_width. 'px (width) x '. $cmsContentMediaVariation->dimension_height.'px (height)</a></li>'."\r"; 
	    }
?>
			</ul>
<?php } ?>
		</div>

		<div class="thumbnailDisplay pull-left"><span class="helper"></span><a href="#" onclick="showDetails('<?php echo Url::toRoute(['media/details','mediaItemId' => $cmsContentMedia->id]) ?>');"><img class="thumbnailImg" src="<?php echo Url::toRoute(['media/thumbnail','mediaItemId' => $cmsContentMedia->id]) ?>" title="<?php echo $cmsContentMedia->meta_description; ?>"/></a></div>
		<div class="thumbnailMetaData">
			<span class="filename">Name: <?php echo $cmsContentMedia->file_name; ?></span>

			<span class="createdDate pull-right">Created: <?php echo $cmsContentMedia->created_datetime; ?></span>
			<span class="dimensions"><span class="glyphicon glyphicon-resize-full" title="dimensions/length"></span> <?php echo $cmsContentMedia->dimension_width.'px x '.$cmsContentMedia->dimension_height.'px'; ?></span>
			<span class="modifiedDate pull-right">Modified: <?php echo $cmsContentMedia->modification_datetime; ?></span>
			<span class="filesize"><span class="glyphicon glyphicon-file" title="filesize"></span> <?php echo Yii::$app->formatter->asShortSize($cmsContentMedia->filesize_bytes); ?></span>
		</div>
	</div>
<?php  
	}
?>
<div id="detailsDialog" title="Media Details"></div>
<?php 
} else {
?>
<h3>Category not found for given id!</h3>
<?php
}
?>

