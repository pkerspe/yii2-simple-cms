<?php
use yii\widgets\ActiveForm;
use yii\bootstrap\ActiveField;
use schallschlucker\simplecms\models\MediaBrowserImageUpload;
use schallschlucker\simplecms\controllers\backend\MediaController;
/* @VAR $model MediaBrowserImageUpload */

if(isset($errors) && count($errors) > 0){
	echo '<div class="error">The following errors occured:<pre>';
	print_r($errors);
	echo '</pre><div>';
}

$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);
?>

	<?php
	echo $form->field($model, 'targetCategoryId')->hiddenInput()->label('');
	/* @var $field ActiveField */
	if(isset($mediaType) && MediaController::$MEDIA_TYPE_IMAGE == $mediaType)
		echo $form->field($model, 'file[]')->fileInput(['multiple' => true,'accept' => 'image/*']);
	else if(isset($mediaType) && MediaController::$MEDIA_TYPE_AUDIO == $mediaType)
		echo $form->field($model, 'file[]')->fileInput(['multiple' => true,'accept' => 'audio/*']);
	else if(isset($mediaType) && MediaController::$MEDIA_TYPE_VIDEO == $mediaType)
		echo $form->field($model, 'file[]')->fileInput(['multiple' => true,'accept' => 'video/*']);
	else 
		echo $form->field($model, 'file[]')->fileInput(['multiple' => true]);
	?>
	
    <button>Submit</button>

<?php ActiveForm::end(); ?>