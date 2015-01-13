<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pn_cms\models\CmsHierarchyItem */

$this->title = Yii::t ( 'simplecms', 'Create {modelClass}', [ 
		'modelClass' => 'Cms Hierarchy Item' 
] );
$this->params ['breadcrumbs'] [] = [ 
		'label' => Yii::t ( 'simplecms', 'Cms Hierarchy Items' ),
		'url' => [ 
				'index' 
		] 
];
$this->params ['breadcrumbs'] [] = $this->title;
?>
<div class="cms-hierarchy-item-create">

	<h1><?= Html::encode($this->title) ?></h1>

    <?=$this->render ( '_form', [ 'model' => $model ] )?>

</div>
