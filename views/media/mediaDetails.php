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
?>
<?php
$javaScript = <<<JS
//JS if needed
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
			<strong>Available Variations for this media item:</strong><br/>
			<div class="btn-group">
    <?php
    if(count($cmsContentMedia->cmsContentMediaVariations) > 0){ 
	    foreach($cmsContentMedia->cmsContentMediaVariations as $cmsContentMediaVariation){
	    	/* @var $cmsContentMediaVariation CmsContentMediaVariation */
	    	echo '<a class="btn btn-info btn-sm" title="media variation with size '.$cmsContentMediaVariation->dimension_width. 'px (width) x '. $cmsContentMediaVariation->dimension_height.'px (height)">'.$cmsContentMediaVariation->dimension_width. 'px x '. $cmsContentMediaVariation->dimension_height.'px <span class="glyphicon glyphicon-info-sign" title="MIME-Type: '.$cmsContentMediaVariation->mime_type." \rFilename: ".$cmsContentMediaVariation->file_name.'"></span></a>'; 
	    }
    } else {
    	echo '<a href="#" class="btn btn-default btn-sm">no variations available yet</a>';
    }
    ?>
    			<a href="#" class="btn btn-success btn-sm">Create variation</a>
    		</div>
    	</div>
    
	    <div class="mediaDetailsImageContainer" id="<?php echo $cmsContentMedia->id; ?>">
			<div class="mediaDisplay"><a href="<?php echo Url::toRoute(['media/get-media','mediaItemId' => $cmsContentMedia->id]) ?>" target="_blank"><img class="mediaImg" src="<?php echo Url::toRoute(['media/get-media','mediaItemId' => $cmsContentMedia->id]) ?>" title="<?php echo $cmsContentMedia->meta_description; ?>"/></a></div>
			<div class="metaData">
				<span class="filename">Name: <?php echo $cmsContentMedia->file_name; ?></span>
				<span class="dimensions"><span class="glyphicon glyphicon-resize-full" title="dimensions/length"></span> <?php echo $cmsContentMedia->dimension_width.'px x '.$cmsContentMedia->dimension_height.'px'; ?></span>
				<span class="filesize"><span class="glyphicon glyphicon-file" title="filesize"></span> <?php echo Yii::$app->formatter->asShortSize($cmsContentMedia->filesize_bytes); ?></span>
				<span class="createdDate">Created: <?php echo $cmsContentMedia->created_datetime; ?></span>
				<span class="createdBy">Created by user with id: <?php echo $cmsContentMedia->createdby_userid; ?></span>
				<span class="modifiedDate">Modified: <?php echo $cmsContentMedia->modification_datetime; ?></span>
				<span class="modifiedBy">Modified by user with id: <?php echo $cmsContentMedia->modification_userid; ?></span>
			</div>
	    </div>
	</div>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>