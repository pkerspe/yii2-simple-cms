<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\BaseFileHelper;
use schallschlucker\simplecms\models\CmsDocument;

/* @var $this 	yii\web\View */
/* @var $model 	schallschlucker\simplecms\models\CmsDocument */
/* @var $form 	ActiveForm */
/* @var $embededForm booelan */

$magicFile = Yii::getAlias ( BaseFileHelper::$mimeMagicFile );
$mimeTypes = require ($magicFile);
$mimeTypeMapping = [];
sort ( $mimeTypes );
foreach ( $mimeTypes as $key => $value ) {
	$mimeTypeMapping [] = ['extension' => $key ,'mimetype'=> $value];
}
?>
<div class="form">
<?php
if (! isset ( $embededForm ))
	$form = ActiveForm::begin ();

if (! isset ( $embededForm )) {
	echo $form->field ( $model, 'language' );
	echo $form->field ( $model, 'createdby_userid' );
	echo $form->field ( $model, 'created_datetime' );
	echo $form->field ( $model, 'modification_userid' );
	echo $form->field ( $model, 'modification_datetime' );
} else {
	?>
	<span class="pull-right"><?= $model->getAttributeLabel('createdby_userid') .': '. $model->createdby_userid ?><br />
	<?= $model->getAttributeLabel('created_datetime') .': '. $model->created_datetime ?></span>
	<div><?= $model->getAttributeLabel('modification_userid') .': '. $model->modification_userid ?></div>
	<div><?= $model->getAttributeLabel('modification_datetime') .': '. $model->modification_datetime ?></div>
<?php
}
echo $form->field ( $model, 'file_name' )->textInput ( [ 
	'maxlength' => 255 
] );
echo $form->field ( $model, 'file_path' )->textInput ( [ 
	'maxlength' => 255 
] );
echo $form->field ( $model, 'presentation_style' )->dropdownList ( [ 
	CmsDocument::PRESENTATION_STYLE_WINDOW => 'full window, let browser handle the file',
	CmsDocument::PRESENTATION_STYLE_EMBEDED => 'embedded in standard layout',
	CmsDocument::PRESENTATION_STYLE_DOWNLOAD => 'send file detached to trigger browsers download link dialog' 
] );
echo $form->field ( $model, 'mime_type' )->dropdownList ( yii\helpers\BaseArrayHelper::map($mimeTypeMapping,'mimetype','mimetype'));
echo $form->field ( $model, 'meta_keywords' )->textInput ( [ 
	'maxlength' => 255 
] );
echo $form->field ( $model, 'meta_description' )->textInput ( [ 
	'maxlength' => 255 
] );
?>
        <div class="form-group">
            <?= Html::submitButton('Submit', ['class' => 'btn btn-primary'])?>
        </div>
    <?php if(!isset($embededForm) ) ActiveForm::end(); ?>
</div>
<!-- _form -->
