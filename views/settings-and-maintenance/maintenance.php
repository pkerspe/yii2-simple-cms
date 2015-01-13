<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use schallschlucker\simplecms\widgets\CmsBackendFunctionBarWidget;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\pn_cms\models\CmsHierarchyItemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t ( 'simplecms', 'CMS Maintenance' );
$this->params ['breadcrumbs'] [] = [
	'label' => Yii::t ( 'simplecms', 'CMS Administration' ),
	'url' => [
		'default/index'
	]
];
$this->params ['breadcrumbs'] [] = $this->title;
echo CmsBackendFunctionBarWidget::widget();
?>

<div class="cms-maintenance">
	<?php $form = ActiveForm::begin(); ?>
	    <?= $form->field($model, 'checkPositionsrecursive')->checkbox() ?>
	
		<div class="form-group">
	    	<?= Html::submitButton('Submit', ['class' => 'btn btn-primary'])?>
	    </div>
	<?php ActiveForm::end(); ?>

<?php if(count($data) > 0) { ?>
	<pre><?php print_r($data) ?></pre>
<?php } ?>
</div>
