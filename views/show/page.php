<?php
use schallschlucker\simplecms\widgets\CmsBootstrapNavbarWidget;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $pageContentModel schallschlucker\simplecms\models\CmsPageContent */
/* @var $isfallbacklanguage boolean */
/* @var $hierarchyItem \schallschlucker\simplecms\models\CmsHierarchyItem */
/* @var $renderTopMenuNavbar boolean */

if ($renderTopMenuNavbar) {
    $widget = new CmsBootstrapNavbarWidget(['displayRootItem' => true, 'enableHoverDropDown' => true]);
    echo $widget->run();
}

if ($pageContentModel->css != null && $pageContentModel->css != '') {
    $this->registerCss($pageContentModel->css, [], 'cmsCustonCssCode');
}
if ($isfallbacklanguage) {
    echo '<p class="fallbackwarning">' . Yii::t('simplecms', 'The page could not be found in the requested language, displaying fallback language instead') . '</p>';
}

if (Yii::$app->controller->module->showBreadcrumbs) {
    $this->params['breadcrumbs'][] = ['label' => \Yii::t('app', Yii::$app->controller->module->rootBreadcrumb), 'url' => ['/docs']];
    $this->params['breadcrumbs'][] = $this->title;
}

echo $pageContentModel->content;

if ($pageContentModel->render_subpage_teasers && !empty($hierarchyItem)) {
    $subpageWidget = new \schallschlucker\simplecms\widgets\CmsSubpageTeaserWidget(['cmsHierarchyItem' => $hierarchyItem]);
    echo $subpageWidget->run();
}

if ($pageContentModel->javascript != null && trim($pageContentModel->javascript) != '') {
    $this->registerJs($pageContentModel->javascript, View::POS_END, 'cmsCustomPageJavascript');
}
?>