<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use schallschlucker\simplecms\models\MenuItemAndContentForm;
use schallschlucker\simplecms\assets\SimpleCmsAsset;
use schallschlucker\simplecms\assets\CkeditorAsset;

SimpleCmsAsset::register ( $this );
CkeditorAsset::register ( $this );
$this->title = Yii::t ( 'app/cms', 'Create new content' );
$this->params ['breadcrumbs'] [] = [ 
		'label' => Yii::t ( 'app/cms', 'CMS Administration' ),
		'url' => [ 
				'index' 
		] 
];
$this->params ['breadcrumbs'] [] = $this->title;
?>
<div class="pn_cms-default-create-menu-and-content">
	<?php if($message != null) { ?>
		<div class="alert alert-success"><?= $message ?></div>
	<?php } ?>

	<span
		class="flag flag_<?= $languageCode['code'] ?> flag-big pull-right"></span>
	<h1><?= $this->title ?></h1>

<?php
$form = ActiveForm::begin ();
echo Yii::t ( 'app/cms', 'Please enter the name for the new language version of this menu item' );
echo $form->field ( $model_wrapperform, 'newMenuName' )->textInput ();
echo Yii::t ( 'app/cms', 'The content type of this menu item has not yet been specified. Please select the content type to continue.' );
echo $form->field ( $model_wrapperform, 'contentType' )->radioList ( [ 
		MenuItemAndContentForm::CONTENT_TYPE_PAGE => 'content page',
		MenuItemAndContentForm::CONTENT_TYPE_DOCUMENT => 'linked document',
		MenuItemAndContentForm::CONTENT_TYPE_URL => 'URL' 
] );
?>
	<div class="form-group">
		<?= Html::submitButton(Yii::t('simplecms', 'Continue'), ['class' => 'btn btn-primary'])?>
	</div>
<?php
ActiveForm::end ();
?>
</div>