<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\modules\pn_cms\models\CmsMenuItemSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="cms-menu-item-search">

    <?php
				
$form = ActiveForm::begin ( [ 
						'action' => [ 
								'index' 
						],
						'method' => 'get' 
				] );
				?>

    <?= $form->field($model, 'id')?>

    <?= $form->field($model, 'cms_hierarchy_item_id')?>

    <?= $form->field($model, 'language')?>

    <?= $form->field($model, 'name')?>

    <?= $form->field($model, 'page_content_id')?>

    <?php // echo $form->field($model, 'document_id') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app/cms', 'Search'), ['class' => 'btn btn-primary'])?>
        <?= Html::resetButton(Yii::t('app/cms', 'Reset'), ['class' => 'btn btn-default'])?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
