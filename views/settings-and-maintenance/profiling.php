<?php
use yii\helpers\Html;
use yii\grid\GridView;
use schallschlucker\simplecms\widgets\CmsBackendFunctionBarWidget;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\pn_cms\models\CmsHierarchyItemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t ( 'simplecms', 'Profiling dummy page' );
$this->params ['breadcrumbs'] [] = [
	'label' => Yii::t ( 'simplecms', 'CMS Administration' ),
	'url' => [
		'default/index'
	]
];
$this->params ['breadcrumbs'] [] = $this->title;
echo CmsBackendFunctionBarWidget::widget();
?>
<div class="cms-profiling">
<pre><?php print_r($data) ?></pre>
</div>
