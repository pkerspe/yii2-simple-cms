<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\modules\pn_cms\models\CmsPageContent */

$this->title = $model->id;
$this->params ['breadcrumbs'] [] = [ 
		'label' => Yii::t ( 'app/cms', 'Cms Page Contents' ),
		'url' => [ 
				'index' 
		] 
];
$this->params ['breadcrumbs'] [] = $this->title;
?>
<div class="cms-page-content-view">

	<h1><?= Html::encode($this->title) ?></h1>

	<p>
        <?= Html::a(Yii::t('simplecms', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary'])?>
        <?=Html::a ( Yii::t ( 'app/cms', 'Delete' ), [ 'delete','id' => $model->id ], [ 'class' => 'btn btn-danger','data' => [ 'confirm' => Yii::t ( 'app/cms', 'Are you sure you want to delete this item?' ),'method' => 'post' ] ] )?>
    </p>

    <?=DetailView::widget ( [ 'model' => $model,'attributes' => [ 'id','language','meta_tags','description','content:ntext','javascript:ntext','css:ntext' ] ] )?>

</div>
