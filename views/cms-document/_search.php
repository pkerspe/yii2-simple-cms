<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\modules\pn_cms\models\CmsDocumentSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="cms-document-search">

    <?php
				
$form = ActiveForm::begin ( [ 
						'action' => [ 
								'index' 
						],
						'method' => 'get' 
				] );
				?>

    <?= $form->field($model, 'id')?>

    <?= $form->field($model, 'language')?>

    <?= $form->field($model, 'file_name')?>

    <?= $form->field($model, 'file_path')?>

    <?= $form->field($model, 'mime_type')?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('simplecms', 'Search'), ['class' => 'btn btn-primary'])?>
        <?= Html::resetButton(Yii::t('simplecms', 'Reset'), ['class' => 'btn btn-default'])?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
