<?php
use yii\widgets\ActiveForm;
use schallschlucker\simplecms\models\MediaBrowserImageUpload;
/* @VAR $model MediaBrowserImageUpload */

if(isset($errors) && count($errors) > 0){
	echo '<div class="error">The following errors occured:<pre>';
	print_r($errors);
	echo '</pre><div>';
}

$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);
?>

	<?= $form->field($model, 'targetCategoryId')->hiddenInput()->label(''); ?>
	<?= $form->field($model, 'file[]')->fileInput(['multiple' => true]) ?>

    <button>Submit</button>

<?php ActiveForm::end(); ?>