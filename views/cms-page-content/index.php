<?php
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\pn_cms\models\CmsPageContenttSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t ( 'app/cms', 'Cms Page Contents' );
$this->params ['breadcrumbs'] [] = $this->title;
?>
<div class="cms-page-content-index">

	<h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?=Html::a ( Yii::t ( 'app/cms', 'Create {modelClass}', [ 'modelClass' => 'Cms Page Content' ] ), [ 'create' ], [ 'class' => 'btn btn-success' ] )?>
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
