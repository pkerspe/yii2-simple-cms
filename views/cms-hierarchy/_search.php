<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\modules\pn_cms\models\CmsHierarchyItemSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="cms-hierarchy-item-search">

    <?php
				
$form = ActiveForm::begin ( [ 
						'action' => [ 
								'index' 
						],
						'method' => 'get' 
				] );
				?>

    <?= $form->field($model, 'id')?>

    <?= $form->field($model, 'parent_id')?>

    <?= $form->field($model, 'position')?>

    <?= $form->field($model, 'display_state')?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app/cms', 'Search'), ['class' => 'btn btn-primary'])?>
        <?= Html::resetButton(Yii::t('app/cms', 'Reset'), ['class' => 'btn btn-default'])?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
