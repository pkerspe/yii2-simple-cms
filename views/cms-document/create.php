<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pn_cms\models\CmsDocument */

$this->title = Yii::t ( 'app/cms', 'Create {modelClass}', [ 
		'modelClass' => 'Cms Document' 
] );
$this->params ['breadcrumbs'] [] = [ 
		'label' => Yii::t ( 'app/cms', 'Cms Documents' ),
		'url' => [ 
				'index' 
		] 
];
$this->params ['breadcrumbs'] [] = $this->title;
?>
<div class="cms-document-create">

	<h1><?= Html::encode($this->title) ?></h1>

    <?=$this->render ( '_form', [ 'model' => $model ] )?>

</div>
