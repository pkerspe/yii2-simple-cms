<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use schallschlucker\simplecms\assets\SimpleCmsAsset;
use schallschlucker\simplecms\models\CmsContentMedia;
use schallschlucker\simplecms\models\CmsContentMediaVariation;

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
    <div>Audio Variation Manager:</div>
    <div>
    	<p>Transformations:</p>
    	NOT YET IMPLEMENTED
    </div>
    <div>
    	<p>Upload new variation</p>
<?php 
    $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);

	echo $form->field($model, 'targetCategoryId')->hiddenInput()->label('');
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
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>