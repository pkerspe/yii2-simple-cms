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
 * @param $navElementClasses string cass classes to set for nav element
 * @param $navbarBrand string text / link to set as navbar brand to display in small screen size drop down menu
 * @param $appendedNavBlock string hmtl code append after last LI within UL
 * @param $appendAfterUL string html code to append after UL in nav bar
 * @param $this->pathToRoot Array assoc array where key is hierarchy item id and value is menu item
 */
class CmsBootstrapNavbarWidget extends Widget {
	
	public $languageId;
	public $enableHoverDropDown = false;
	public $displayRootItem = true;
	public $navElementClasses = 'navbar navbar-default';
	public $mainULElementClasses = 'nav navbar-nav';
	public $navbarBrand = '';
	public $appendedNavBlock = '';
	public $appendAfterUL = '';
	public $activeItemId = -1;
	public $activeItemLiClass = 'active';
	public $pathToRoot = [];
	
	public function init() {
		if($this->languageId == null){
		    if(Yii::$app->getModule('simplecms_frontend') == null)
		        throw new \Exception('Could not find module with name simplecms_frontend. Make sure you included the moduel in your configuration');
			$this->languageId = Frontend::getLanguageManagerStatic()->getLanguageIdForString(Yii::$app->language);
		}
		parent::init ();
	}
	
	public function run() {
		$activeLiClassAttribute = ' class="'.$this->activeItemLiClass.'"';
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
<nav class="$this->navElementClasses">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#cms-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
TEXT;
	  $widgetHtml .= $this->navbarBrand;
	  $widgetHtml .= <<<TEXT
    </div>

    <div class="collapse navbar-collapse" id="cms-navbar-collapse-1">
		<ul class="$this->mainULElementClasses">
TEXT;
		/* @var $simpleHierarchyItem SimpleHierarchyItem */
		$simpleHierarchyItem = NavigationController::getRootHierarchyItemCached($this->languageId);
		$areaDropDownClasses = '';
		$areaDropDownAttributes = '';
		
		if($this->displayRootItem){
			$widgetHtml .= '<li'.(($this->activeItemId == $simpleHierarchyItem->id || key_exists($simpleHierarchyItem->id, $this->pathToRoot))? $activeLiClassAttribute : '').'>'.$simpleHierarchyItem->getLinkTag().'</li>';
		}
		
		if(!$this->enableHoverDropDown){
			$areaDropDownClasses = 'dropdown-toggle';
			$areaDropDownAttributes = ' data-toggle="dropdown" ';
		}
		foreach($simpleHierarchyItem->getAllChildren() as $areaItem){
			if(count($areaItem->children) > 0){
				$widgetHtml .= '<li class="dropdown'.(($this->activeItemId == $areaItem->id || key_exists($areaItem->id, $this->pathToRoot))? ' '.$this->activeItemLiClass: '').'">'.$areaItem->getLinkTag($areaDropDownClasses,$areaDropDownAttributes.' role="button" aria-expanded="false"','<span class="caret"></span>').'<ul class="dropdown-menu" role="menu">';
				foreach($areaItem->getAllChildren() as $areaChildItem){
					$widgetHtml .= '<li'.(($this->activeItemId == $areaChildItem->id || key_exists($areaChildItem->id, $this->pathToRoot))? $activeLiClassAttribute : '').'>'.$areaChildItem->getLinkTag().'</li>';
				}
				$widgetHtml .= '</ul></li>';
			} else {
				$widgetHtml .= '<li'.(($this->activeItemId == $areaItem->id || key_exists($areaItem->id, $this->pathToRoot))? $activeLiClassAttribute : '').'>'.$areaItem->getLinkTag().'</li>';
			}
		}
		
		$widgetHtml .= <<<TEXT
		$this->appendedNavBlock
      	</ul>
        $this->appendAfterUL
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
TEXT;
		$widgetHtml .= '';
		return $widgetHtml;
	}
}
?>