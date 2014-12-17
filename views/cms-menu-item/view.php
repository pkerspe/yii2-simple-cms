<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\modules\pn_cms\models\CmsMenuItem */

$this->title = $model->name;
$this->params ['breadcrumbs'] [] = [ 
		'label' => Yii::t ( 'app/cms', 'Cms Menu Items' ),
		'url' => [ 
				'index' 
		] 
];
$this->params ['breadcrumbs'] [] = $this->title;
?>
<div class="cms-menu-item-view">

	<h1><?= Html::encode($this->title) ?></h1>

	<p>
        <?= Html::a(Yii::t('app/cms', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary'])?>
        <?=Html::a ( Yii::t ( 'app/cms', 'Delete' ), [ 'delete','id' => $model->id ], [ 'class' => 'btn btn-danger','data' => [ 'confirm' => Yii::t ( 'app/cms', 'Are you sure you want to delete this item?' ),'method' => 'post' ] ] )?>
    </p>

    <?=DetailView::widget ( [ 'model' => $model,'attributes' => [ 'id','cms_hierarchy_item_id','language','name','page_content_id','document_id' ] ] )?>

</div>
