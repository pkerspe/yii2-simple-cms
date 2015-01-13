<?php
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\pn_cms\models\CmsHierarchyItemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t ( 'simplecms', 'Cms Hierarchy Items' );
$this->params ['breadcrumbs'] [] = $this->title;
?>
<div class="cms-hierarchy-item-index">

	<h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?=Html::a ( Yii::t ( 'simplecms', 'Create {modelClass}', [ 'modelClass' => 'Cms Hierarchy Item' ] ), [ 'create' ], [ 'class' => 'btn btn-success' ] )?>
    </p>

    <?=GridView::widget ( [ 'dataProvider' => $dataProvider,'filterModel' => $searchModel,'columns' => [ [ 'class' => 'yii\grid\SerialColumn' ],'id','parent_id','position','display_state',[ 'class' => 'yii\grid\ActionColumn' ] ] ] );?>

</div>
