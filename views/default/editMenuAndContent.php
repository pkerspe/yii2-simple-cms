<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use schallschlucker\simplecms\models\MenuItemAndContentForm;
use schallschlucker\simplecms\assets\SimpleCmsAsset;
use schallschlucker\simplecms\assets\CkeditorAsset;
use schallschlucker\simplecms\controllers\backend\MediaController;

//set the path of the image browser dialog
echo ('<script type="text/javascript">var imageBrowserUrl = "'.Url::toRoute(['media/mediabrowser','mediatype'=>'IMAGE','activeCategoryId' => MediaController::$MEDIA_IMAGE_BASE_CATEGORY_ID]).'";</script>');

SimpleCmsAsset::register ( $this );
CkeditorAsset::register ( $this );
$this->title = Yii::t ( 'app/cms', 'Edit language version' );
$this->params ['breadcrumbs'] [] = [ 
		'label' => Yii::t ( 'app/cms', 'CMS Administration' ),
		'url' => [ 
				'default/index' 
		] 
];
$this->params ['breadcrumbs'] [] = $this->title;
?>
<div class="pn_cms-default-edit-menu-and-content">
	<?php if($message != null) { ?>
		<div class="alert alert-success"><?= $message ?></div>
	<?php } ?>

	<span
		class="flag flag_<?= $languageCode['code'] ?> flag-big pull-right"></span>
	<h1><?= $this->title ?></h1>
	
<?php
$form = ActiveForm::begin ();
if ($model_wrapperform->contentType == MenuItemAndContentForm::CONTENT_TYPE_UNDEFINED) {
	echo Yii::t ( 'app/cms', 'The content type of this menu item has not yet been specified. Please select the content type to continue.' );
	echo $form->field ( $model_wrapperform, 'contentType' )->radioList ( [ 
			MenuItemAndContentForm::CONTENT_TYPE_PAGE => 'content page',
			MenuItemAndContentForm::CONTENT_TYPE_DOCUMENT => 'linked document',
			MenuItemAndContentForm::CONTENT_TYPE_URL => 'URL' 
	] );
	?>
		<div class="form-group">
            <?= Html::submitButton(Yii::t('app/cms', 'Continue'), ['class' => 'btn btn-primary'])?>
        </div>
<?php
} else {
	if ($model_wrapperform->contentType == MenuItemAndContentForm::CONTENT_TYPE_URL) {
		echo $form->field ( $model_wrapperform, 'contentType' )->label ( '' )->hiddenInput ();
		echo $this->render ( '../cms-menu-item/_form', [ 
				'model' => $model_menu,
				'embededForm' => true,
				'form' => $form 
		] );
	} else if ($model_wrapperform->contentType == MenuItemAndContentForm::CONTENT_TYPE_PAGE) {
		echo $form->field ( $model_wrapperform, 'contentType' )->label ( '' )->hiddenInput ();
		echo $this->render ( '../cms-menu-item/_form', [ 
				'model' => $model_menu,
				'embededForm' => true,
				'form' => $form,
				'hideDirectUrl' => true 
		] );
		echo $this->render ( '../cms-page-content/_form', [ 
				'model' => $model_content,
				'embededForm' => true,
				'form' => $form 
		] );
	} else if ($model_wrapperform->contentType == MenuItemAndContentForm::CONTENT_TYPE_DOCUMENT) {
		echo $form->field ( $model_wrapperform, 'contentType' )->label ( '' )->hiddenInput ();
		echo $this->render ( '../cms-menu-item/_form', [ 
				'model' => $model_menu,
				'embededForm' => true,
				'form' => $form,
				'hideDirectUrl' => true 
		] );
		echo $this->render ( '../cms-document/_form', [ 
				'model' => $model_document,
				'embededForm' => true,
				'form' => $form 
		] );
	} else {
	}
}
ActiveForm::end ();
?>
</div>