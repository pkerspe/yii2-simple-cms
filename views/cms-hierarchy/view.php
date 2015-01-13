<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\modules\pn_cms\models\CmsHierarchyItem */

$this->title = $model->id;
$this->params ['breadcrumbs'] [] = [ 
		'label' => Yii::t ( 'simplecms', 'Cms Hierarchy Items' ),
		'url' => [ 
				'index' 
		] 
];
$this->params ['breadcrumbs'] [] = $this->title;
?>
<div class="cms-hierarchy-item-view">

	<h1><?= Html::encode($this->title) ?></h1>

	<p>
        <?= Html::a(Yii::t('simplecms', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary'])?>
        <?=Html::a ( Yii::t ( 'simplecms', 'Delete' ), [ 'delete','id' => $model->id ], [ 'class' => 'btn btn-danger','data' => [ 'confirm' => Yii::t ( 'simplecms', 'Are you sure you want to delete this item?' ),'method' => 'post' ] ] )?>
    </p>

    <?=DetailView::widget ( [ 'model' => $model,'attributes' => [ 'id','parent_id','position','display_state' ] ] )?>

</div>
