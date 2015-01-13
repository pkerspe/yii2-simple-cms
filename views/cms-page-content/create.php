<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pn_cms\models\CmsPageContent */

$this->title = Yii::t ( 'simplecms', 'Create {modelClass}', [ 
		'modelClass' => 'Cms Page Content' 
] );
$this->params ['breadcrumbs'] [] = [ 
		'label' => Yii::t ( 'simplecms', 'Cms Page Contents' ),
		'url' => [ 
				'index' 
		] 
];
$this->params ['breadcrumbs'] [] = $this->title;
?>
<div class="cms-page-content-create">

	<h1><?= Html::encode($this->title) ?></h1>

    <?=$this->render ( '_form', [ 'model' => $model ] )?>

</div>
