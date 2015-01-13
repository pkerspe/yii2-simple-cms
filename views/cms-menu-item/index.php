<?php
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\pn_cms\models\CmsMenuItemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t ( 'simplecms', 'Cms Menu Items' );
$this->params ['breadcrumbs'] [] = $this->title;
?>
<div class="cms-menu-item-index">

	<h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?=Html::a ( Yii::t ( 'simplecms', 'Create {modelClass}', [ 'modelClass' => 'Cms Menu Item' ] ), [ 'create' ], [ 'class' => 'btn btn-success' ] )?>
    </p>

    <?=GridView::widget ( [ 'dataProvider' => $dataProvider,'filterModel' => $searchModel,'columns' => [ [ 'class' => 'yii\grid\SerialColumn' ],'id','cms_hierarchy_item_id','language','name','page_content_id','document_id',[ 'class' => 'yii\grid\ActionColumn' ] ] ] );?>

</div>
