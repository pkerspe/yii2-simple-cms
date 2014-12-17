<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\modules\pn_cms\models\CmsHierarchyItem */
/* @var $form ActiveForm */
?>
<div class="form">

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'parent_id')?>
        <?= $form->field($model, 'position')?>
        <?= $form->field($model, 'display_state')?>
    
        <div class="form-group">
            <?= Html::submitButton('Submit', ['class' => 'btn btn-primary'])?>
        </div>
    <?php ActiveForm::end(); ?>

</div>
<!-- _form -->
