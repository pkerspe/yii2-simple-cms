<?php

namespace schallschlucker\simplecms\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use schallschlucker\simplecms\controllers\backend\CmsHierarchyController;
use schallschlucker\simplecms\controllers\backend\DefaultController;
use yii\helpers\Url;
use schallschlucker\simplecms\controllers\frontend\NavigationController;
use schallschlucker\simplecms\models\SimpleHierarchyItem;

class CmsBootstrapNavbarWidget extends Widget {
	
	public langauageId;
	
	
	public function init() {
		if($this->langauageId == null)
			$this->languageId = Yii::$app->controller->module->getLanguageManager()->getLanguageIdForString(Yii::$app->language);
		
		parent::init ();
	}
	
	public function run() {
		$widgetHtml = <<<TEXT
<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="cms-navbar-collapse-1">
		<ul class="nav navbar-nav">
TEXT;
		$simpleHierarchyItem = NavigationController::getRootHierarchyItemCached($this->langauageId);

		
// 		$index = Url::toRoute('default/index');
		
		foreach($simpleHierarchyItem->getAllChildren() as $areaItem){
			if(count($areaItem->children) > 0){
				$widgetHtml .= '<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'.$areaItem->title.'<span class="caret"></span></a><ul class="dropdown-menu" role="menu">';
				foreach($areaItem->getAllChildren() as $areaChildItem){
						$widgetHtml .= '<li><a href="'.$areaChildItem->getFormattedUrl().'">'.$areaChildItem->title.'</a></li>';
				}
				$widgetHtml .= '</ul></li>';
			} else {
				$widgetHtml .= '<li><a href="'.$areaItem->getFormattedUrl().'">'.$areaItem->title.'</a></li>';
			}
		}
		
		$widgetHtml .= <<<TEXT
      	</ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
TEXT;
		$widgetHtml .= '';
		return $widgetHtml;
	}
}
?>