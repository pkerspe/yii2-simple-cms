<?php
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\pn_cms\models\CmsHierarchyItemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t ( 'app/cms', 'Profiling dummy page' );
$this->params ['breadcrumbs'] [] = $this->title;
?>
<div class="cms-hierarchy-item-index">
<div><?= $counter ?></div>
</div>
