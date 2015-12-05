<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\modules\pn_cms\models\CmsMenuItem */
/* @var $form ActiveForm */
?>
<div class="form">

    <?php if(!isset($embededForm) ) $form = ActiveForm::begin(); ?>

	<?php if(!isset($embededForm) ) { ?>
        <?= $form->field($model, 'cms_hierarchy_item_id')?>
        <?= $form->field($model, 'language')?>
        <?= $form->field($model, 'page_content_id')?>
        <?= $form->field($model, 'document_id')?>
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
	
	<div class="row inline-form">
        <div class="col-md-6">
            <?= $form->field($model, 'name')?>
            <?= $form->field($model, 'link_target')?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'alias')?>
            <?= $form->field($model, 'link_css_class')?>
	   </div>
    </div>

    <?php if(!isset($hideDirectUrl) || !$hideDirectUrl ) { ?>
        <?= $form->field($model, 'direct_url')?>
    <?php } ?>
    <div class="form-group">
            <?= Html::submitButton('Submit', ['class' => 'btn btn-primary'])?>
    </div>
<?php if(!isset($embededForm) ) ActiveForm::end(); ?>

</div>
<!-- _form -->
