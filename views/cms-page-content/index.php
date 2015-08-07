<?php
use yii\helpers\Html;
use yii\web\View;
use yii\grid\GridView;
use schallschlucker\simplecms\widgets\CmsBackendFunctionBarWidget;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\pn_cms\models\CmsPageContenttSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t ( 'simplecms', 'Cms Page Contents' );
$this->params ['breadcrumbs'] [] = [
	'label' => Yii::t ( 'simplecms', 'CMS Administration' ),
	'url' => [
		'default/index'
	]
];
$this->params ['breadcrumbs'] [] = $this->title;

CmsBackendFunctionBarWidget::widget();
?>
<div class="cms-page-content-index">

	<h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?=Html::a ( Yii::t ( 'simplecms', 'Create {modelClass}', [ 'modelClass' => 'Cms Page Content' ] ), [ 'create' ], [ 'class' => 'btn btn-success' ] )?>
    </p>

    <?=GridView::widget ( 
    [ 
    	'dataProvider' => $dataProvider,
    	'filterModel' => $searchModel,
    	'columns' => [
    			[ 'class' => 'yii\grid\SerialColumn' ],
    			'id','language','description',
    			//'content:ntext',// 'javascript:ntext',// 'css:ntext','meta_tags',
    			[ 'class' => 'yii\grid\ActionColumn' ] ] ] ); 
    ?>

</div>
