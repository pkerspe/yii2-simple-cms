<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pn_cms\models\CmsPageContent */

$this->title = Yii::t ( 'simplecms', 'Update {modelClass}: ', [ 
		'modelClass' => 'Cms Page Content' 
] ) . ' ' . $model->id;
$this->params ['breadcrumbs'] [] = [ 
		'label' => Yii::t ( 'simplecms', 'Cms Page Contents' ),
		'url' => [ 
				'index' 
		] 
];
$this->params ['breadcrumbs'] [] = [ 
		'label' => $model->id,
		'url' => [ 
				'view',
				'id' => $model->id 
		] 
];
$this->params ['breadcrumbs'] [] = Yii::t ( 'simplecms', 'Update' );
?>
<div class="cms-page-content-update">

	<h1><?= Html::encode($this->title) ?></h1>

    <?=$this->render ( '_form', [ 'model' => $model ] )?>

</div>
