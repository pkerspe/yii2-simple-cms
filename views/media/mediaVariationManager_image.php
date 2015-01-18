<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use schallschlucker\simplecms\assets\SimpleCmsAsset;
use schallschlucker\simplecms\models\CmsContentMedia;
use schallschlucker\simplecms\models\CmsContentMediaVariation;
use schallschlucker\simplecms\controllers\backend\MediaController;

/* @var $this yii\web\View */
/* @var $mediaItem CmsContentMedia */
/* @var $model MediaVariationManagerUpload */
SimpleCmsAsset::register ( $this );

$javaScript = <<<JS
//JS if needed

JS;

$this->registerJs ( $javaScript, View::POS_END, 'cmsMediaVariationManager' );
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
    <h3>Image Variation Manager</h3>
	<div role="tabpanel">
	
	  <!-- Nav tabs -->
	  <ul class="nav nav-tabs" role="tablist">
	    <li role="presentation" class="active"><a href="#create" aria-controls="create" role="tab" data-toggle="tab">Create resized version</a></li>
	    <li role="presentation"><a href="#upload" aria-controls="settings" role="tab" data-toggle="tab">Upload new variation</a></li>
	  </ul>
	
	  <div class="tab-content" style="border-left: 1px #ddd solid;border-right: 1px #ddd solid;border-bottom: 1px #ddd solid;padding:10px;">
	    <div role="tabpanel" class="tab-pane active" id="create">
	        <p>Transformations:</p>
	    	<?php Yii::t('simplecms', 'Create resized version of original image'); ?>
	    	<p>Maintain dimension relations<p>
	    	<p>new Width<p>
	    	<p>new Height<p>
	    	<p>use bounding box<p>
	    	<p>use fill color for background (gif or png with aplha channel, or when reiszed image does not fill bounding box:<p>
	    	<p>output format</p>
	    	<p>compression factor (jpeg only)</p>
		    
	    </div>
	    <div role="tabpanel" class="tab-pane" id="upload">
    	<p>Upload new variation</p>
		<?php 
		    $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);
		
			echo $form->field($model, 'parentMediaId')->hiddenInput()->label('');
			/* @var $field ActiveField */
			if($mediaItem->media_type == MediaController::$MEDIA_TYPE_IMAGE)
				echo $form->field($model, 'file[]')->fileInput(['multiple' => true,'accept' => 'image/*']);
			else if($mediaItem->media_type == MediaController::$MEDIA_TYPE_AUDIO)
				echo $form->field($model, 'file[]')->fileInput(['multiple' => true,'accept' => 'audio/*']);
			else if($mediaItem->media_type == MediaController::$MEDIA_TYPE_VIDEO)
				echo $form->field($model, 'file[]')->fileInput(['multiple' => true,'accept' => 'video/*']);
			else 
				echo $form->field($model, 'file[]')->fileInput(['multiple' => true]);
			?>
		    <button type="submit" class="btn btn-success">Submit</button>
		
		<?php ActiveForm::end(); ?>	   
	    </div>
	  </div>
	</div>
    
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>