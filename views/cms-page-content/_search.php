<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\modules\pn_cms\models\CmsPageContenttSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="cms-page-content-search">

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

    <?= $form->field($model, 'meta_tags')?>

    <?= $form->field($model, 'description')?>

    <?= $form->field($model, 'content')?>

    <?php // echo $form->field($model, 'javascript') ?>

    <?php // echo $form->field($model, 'css') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app/cms', 'Search'), ['class' => 'btn btn-primary'])?>
        <?= Html::resetButton(Yii::t('app/cms', 'Reset'), ['class' => 'btn btn-default'])?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
