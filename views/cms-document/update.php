<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pn_cms\models\CmsDocument */

$this->title = Yii::t ( 'app/cms', 'Update {modelClass}: ', [ 
		'modelClass' => 'Cms Document' 
] ) . ' ' . $model->id;
$this->params ['breadcrumbs'] [] = [ 
		'label' => Yii::t ( 'app/cms', 'Cms Documents' ),
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
$this->params ['breadcrumbs'] [] = Yii::t ( 'app/cms', 'Update' );
?>
<div class="cms-document-update">

	<h1><?= Html::encode($this->title) ?></h1>

    <?=$this->render ( '_form', [ 'model' => $model ] )?>

</div>
