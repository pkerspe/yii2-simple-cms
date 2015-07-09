<?php
/*
 * This file is part of the simple cms project for Yii2
 *
 * (c) Schallschlucker Agency Paul Kerspe - project homepage <https://github.com/pkerspe/yii2-simple-cms>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace schallschlucker\simplecms\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use schallschlucker\simplecms\controllers\backend\CmsHierarchyController;
use schallschlucker\simplecms\controllers\backend\DefaultController;
use yii\helpers\Url;
use schallschlucker\simplecms\controllers\frontend\NavigationController;
use schallschlucker\simplecms\models\SimpleHierarchyItem;
use schallschlucker\simplecms\Frontend;
/**
 * A widget to render a complete bootrstrap navbar for all published cms menu items.
 * Only areas and their children will be rendered (two level navigation) due to the fact, that bootstrap does not support more levels.
 * The following parameters are supported:
 * @param $languageId integer the id of the language to render the navigation for (the language of the menu items). If paramete is omitted, the current app language (as returned by Yii::$app->language) is used and converted to languageid via the configured languageManager
 * @param $enableHoverDropDown boolean true inidcates that area items should be clickable and dorpdown menu will be display on hover state. false results in area items not being links but instead only open the submenu drawer on click.
 * @param $displayRootItem boolean true displays the root item, false supresses the root item
 */
class CmsBootstrapNavbarWidget extends Widget {
	
	public $languageId;
	public $enableHoverDropDown = false;
	public $displayRootItem = true;
	
	public function init() {
		if($this->languageId == null){
		    if(Yii::$app->getModule('simplecms_frontend') == null)
		        throw new \Exception('Could not find module with name simplecms_frontend. Make sure you included the moduel in your configuration');
			$this->languageId = Frontend::getLanguageManagerStatic()->getLanguageIdForString(Yii::$app->language);
		}
		parent::init ();
	}
	
	public function run() {
		$widgetHtml = '';
		if($this->enableHoverDropDown){
		$widgetHtml .= <<<TEXT
			<style>
			ul.nav li.dropdown:hover ul.dropdown-menu{
				display: block;
				margin-top:0px
			}
			</style>
TEXT;
		}
		
		$widgetHtml .= <<<TEXT

<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#cms-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="cms-navbar-collapse-1">
		<ul class="nav navbar-nav">
TEXT;
		/* @var $simpleHierarchyItem SimpleHierarchyItem */
		$simpleHierarchyItem = NavigationController::getRootHierarchyItemCached($this->languageId);
		$areaDropDownClasses = '';
		$areaDropDownAttributes = '';
		
		if($this->displayRootItem){
			$widgetHtml .= '<li>'.$simpleHierarchyItem->getLinkTag().'</li>';
		}
		
		if(!$this->enableHoverDropDown){
			$areaDropDownClasses = 'dropdown-toggle';
			$areaDropDownAttributes = ' data-toggle="dropdown" ';
		}
		foreach($simpleHierarchyItem->getAllChildren() as $areaItem){
			if(count($areaItem->children) > 0){
				$widgetHtml .= '<li class="dropdown">'.$areaItem->getLinkTag($areaDropDownClasses,$areaDropDownAttributes.' role="button" aria-expanded="false"','<span class="caret"></span>').'<ul class="dropdown-menu" role="menu">';
				foreach($areaItem->getAllChildren() as $areaChildItem){
					$widgetHtml .= '<li>'.$areaChildItem->getLinkTag().'</li>';
				}
				$widgetHtml .= '</ul></li>';
			} else {
				$widgetHtml .= '<li>'.$areaItem->getLinkTag().'</li>';
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