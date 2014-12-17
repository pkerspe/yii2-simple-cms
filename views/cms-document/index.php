<?php
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\pn_cms\models\CmsDocumentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t ( 'app/cms', 'Cms Documents' );
$this->params ['breadcrumbs'] [] = $this->title;
?>
<div class="cms-document-index">

	<h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?=Html::a ( Yii::t ( 'app/cms', 'Create {modelClass}', [ 'modelClass' => 'Cms Document' ] ), [ 'create' ], [ 'class' => 'btn btn-success' ] )?>
    </p>

    <?=GridView::widget ( [ 'dataProvider' => $dataProvider,'filterModel' => $searchModel,'columns' => [ [ 'class' => 'yii\grid\SerialColumn' ],'id','language','file_name','file_path','mime_type',[ 'class' => 'yii\grid\ActionColumn' ] ] ] );?>

</div>
