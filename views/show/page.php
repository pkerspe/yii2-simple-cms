<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use schallschlucker\simplecms\widgets\CmsBootstrapNavbarWidget;
/* @var $this yii\web\View */
/* @var $pageContentModel schallschlucker\simplecms\models\CmsPageContent */
/* @var $isfallbacklanguage boolean */
/* @var $renderTopMenuNavbar boolean */

if($renderTopMenuNavbar){
    $widget = new CmsBootstrapNavbarWidget(['displayRootItem'=>true,'enableHoverDropDown'=>true]);
    echo $widget->run();
}

if($pageContentModel->css != null && $pageContentModel->css != ''){
	$this->registerCss ( $pageContentModel->css,[],'cmsCustonCssCode' );
}
if($isfallbacklanguage){
	echo '<p class="fallbackwarning">'.Yii::t('simplecms', 'The page could not be found in the requested language, displaying fallback language instead').'</p>';
}

echo $pageContentModel->content;

if($pageContentModel->javascript != null && trim($pageContentModel->javascript) != ''){
	$this->registerJs ( $pageContentModel->javascript, View::POS_END, 'cmsCustomPageJavascript' );
}
?>