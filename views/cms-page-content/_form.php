<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\modules\pn_cms\models\CmsPageContent */
/* @var $form ActiveForm */
?>
<div class="form">

    <?php if(!isset($embededForm) ) $form = ActiveForm::begin(); ?>

	<?php if(!isset($embededForm) ) { ?>
		<?= $form->field($model, 'language')?>
		<?= $form->field($model, 'createdby_userid')?>
		<?= $form->field($model, 'created_datetime')?>
		<?= $form->field($model, 'modification_userid')?>
		<?= $form->field($model, 'modification_datetime')?>
	<?php } else { ?>
		<span class="pull-right"><?= $model->getAttributeLabel('createdby_userid') .': '. $model->createdby_userid ?><br />
		<?= $model->getAttributeLabel('created_datetime') .': '. $model->created_datetime ?></span>
	<div><?= $model->getAttributeLabel('modification_userid') .': '. $model->modification_userid ?></div>
	<div><?= $model->getAttributeLabel('modification_datetime') .': '. $model->modification_datetime ?></div>
	<?php  } ?>
        <?= $form->field($model, 'content')->textarea(['class' => 'ckeditor'])?>
        <?= $form->field($model, 'javascript')->textarea()?>
        <?= $form->field($model, 'css')->textarea()?>

        <?= $form->field($model, 'description')->textarea(['maxlength' => 500])?>
        <?= $form->field($model, 'htmlTitle')->textarea(['maxlength' => 255])?>?>
        <?= $form->field($model, 'meta_keywords')->textarea(['maxlength' => 255])?>
        <?= $form->field($model, 'metatags_general')->textarea(['maxlength' => 500])?>
    
        <div class="form-group">
            <?= Html::submitButton('Submit', ['class' => 'btn btn-primary'])?>
        </div>
    <?php if(!isset($embededForm) ) ActiveForm::end(); ?>

</div>
<!-- _form -->
